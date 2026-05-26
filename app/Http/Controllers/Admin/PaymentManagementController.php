<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Currency;
use App\Models\InstallmentDetail;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentManagementController extends Controller
{
    /**
     * Listado global de Gestión de Pagos.
     * Muestra todas las aplicaciones con costo de programa definido.
     */
    public function index(Request $request)
    {
        $query = Application::query()
            ->with(['user', 'program']);

        // Fase 3: filtro de costo (default 'with_cost' para no romper comportamiento previo)
        $costFilter = $request->input('cost_filter', 'with_cost');
        if ($costFilter === 'with_cost') {
            $query->whereNotNull('total_cost')->where('total_cost', '>', 0);
        } elseif ($costFilter === 'without_cost') {
            $query->where(function ($q) {
                $q->whereNull('total_cost')->orWhere('total_cost', '<=', 0);
            });
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($programId = $request->input('program_id')) {
            $query->where('program_id', $programId);
        }

        if ($year = $request->input('year')) {
            $query->whereYear(DB::raw('COALESCE(cost_manual_date, created_at)'), $year);
        }

        $applications = $query->orderByDesc(DB::raw('COALESCE(cost_manual_date, created_at)'))
            ->paginate(25)
            ->withQueryString();

        // Precompute paid totals (verified only)
        $paidByApp = Payment::whereIn('application_id', $applications->pluck('id'))
            ->where('status', 'verified')
            ->selectRaw('application_id, SUM(COALESCE(converted_amount, amount)) as paid')
            ->groupBy('application_id')
            ->pluck('paid', 'application_id');

        $programs = Program::where('is_active', true)->orderBy('name')->get();
        $years = Application::selectRaw('YEAR(COALESCE(cost_manual_date, created_at)) as y')
            ->whereNotNull('total_cost')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y');

        return view('admin.payment-management.index', compact('applications', 'paidByApp', 'programs', 'years'));
    }

    /**
     * Perfil financiero de una aplicación.
     */
    public function show($applicationId)
    {
        $application = Application::with(['user', 'program', 'payments.currency', 'payments.verifiedBy'])
            ->findOrFail($applicationId);

        $payments = $application->payments()->orderByDesc('payment_date')->orderByDesc('id')->get();
        $verifiedTotal = $payments->where('status', 'verified')->sum(fn ($p) => $p->converted_amount ?? $p->amount);
        $pendingTotal = $payments->where('status', 'pending')->sum('amount');
        $balance = ($application->total_cost ?? 0) - $verifiedTotal;

        $installmentPlan = PaymentInstallment::with('installmentDetails')
            ->where('application_id', $applicationId)
            ->where('status', '!=', 'cancelled')
            ->first();

        $currencies = Currency::all();

        return view('admin.payment-management.show', compact(
            'application',
            'payments',
            'verifiedTotal',
            'pendingTotal',
            'balance',
            'installmentPlan',
            'currencies'
        ));
    }

    /**
     * Crear o actualizar el costo del programa para una aplicación.
     */
    public function updateCost(Request $request, $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        $validated = $request->validate([
            'total_cost' => 'required|numeric|min:0',
            'cost_currency' => 'required|in:USD,PYG',
            'payment_deadline' => 'nullable|date',
            'cost_manual_date' => 'nullable|date',
        ]);

        if (is_null($application->total_cost) || $application->total_cost == 0) {
            $validated['cost_locked_at'] = now();
        }

        $application->update($validated);

        return redirect()
            ->route('admin.payment-management.show', $application->id)
            ->with('success', 'Costo del programa actualizado.');
    }

    /**
     * Crear plan de cuotas mensuales para una aplicación.
     * Fase 3: el total_amount debe coincidir con el total_cost de la Application
     * (tolerancia ±1 unidad por redondeo).
     */
    public function storeInstallmentPlan(Request $request, $applicationId)
    {
        $application = Application::with('program')->findOrFail($applicationId);

        $validated = $request->validate([
            'plan_name' => 'nullable|string|max:120',
            'total_installments' => 'required|integer|min:2|max:24',
            'total_amount' => 'required|numeric|min:0',
            'first_due_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        // Validar coherencia con costo de la aplicación.
        if ($application->total_cost && abs($validated['total_amount'] - $application->total_cost) > 1) {
            return back()->withInput()->with('error', sprintf(
                'El total del plan (%s) debe coincidir con el costo del programa (%s).',
                number_format($validated['total_amount'], 2),
                number_format($application->total_cost, 2)
            ));
        }

        $existing = PaymentInstallment::where('application_id', $applicationId)
            ->where('status', '!=', 'cancelled')
            ->first();
        if ($existing) {
            return back()->with('error', 'Ya existe un plan de cuotas activo para esta aplicación.');
        }

        DB::transaction(function () use ($validated, $application) {
            $plan = PaymentInstallment::create([
                'application_id' => $application->id,
                'user_id' => $application->user_id,
                'program_id' => $application->program_id,
                'plan_name' => $validated['plan_name'] ?: 'Plan de Cuotas',
                'total_installments' => $validated['total_installments'],
                'total_amount' => $validated['total_amount'],
                'currency_id' => $validated['currency_id'],
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            $perInstallment = round($validated['total_amount'] / $validated['total_installments'], 2);
            $firstDue = \Carbon\Carbon::parse($validated['first_due_date']);

            for ($i = 1; $i <= $validated['total_installments']; $i++) {
                InstallmentDetail::create([
                    'payment_installment_id' => $plan->id,
                    'installment_number' => $i,
                    'amount' => $perInstallment,
                    'due_date' => $firstDue->copy()->addMonthsNoOverflow($i - 1),
                    'status' => 'pending',
                ]);
            }
        });

        return redirect()
            ->route('admin.payment-management.show', $application->id)
            ->with('success', 'Plan de cuotas creado.');
    }

    /**
     * Marcar una cuota como pagada, vinculándola a un Payment verificado existente.
     * Fase 3: cierra el gap entre Plan de Cuotas y los Payments reales.
     */
    public function markInstallmentPaid(Request $request, $detailId)
    {
        $detail = InstallmentDetail::with('paymentInstallment')->findOrFail($detailId);

        $validated = $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);

        if ($payment->application_id !== $detail->paymentInstallment->application_id) {
            return back()->with('error', 'El pago no pertenece a la misma aplicación que esta cuota.');
        }

        if ($payment->status !== 'verified') {
            return back()->with('error', 'Solo pagos verificados pueden marcar una cuota como pagada.');
        }

        $detail->markAsPaid(null, $payment);

        ActivityLog::log('payment_management')
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->withAction('installment_marked_paid')
            ->withProperties([
                'installment_detail_id' => $detail->id,
                'installment_number' => $detail->installment_number,
                'payment_id' => $payment->id,
                'amount' => $detail->amount,
            ])
            ->log("Cuota #{$detail->installment_number} marcada como pagada con Payment #{$payment->id}");

        return back()->with('success', "Cuota #{$detail->installment_number} marcada como pagada.");
    }

    /**
     * Asignar el mismo costo de programa a múltiples aplicaciones a la vez.
     * Fase 3: reemplaza el flujo uno-a-uno del Perfil Financiero por una herramienta masiva.
     */
    public function bulkAssignCost(Request $request)
    {
        $validated = $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'integer|exists:applications,id',
            'total_cost' => 'required|numeric|min:0',
            'cost_currency' => 'required|in:USD,PYG',
            'payment_deadline' => 'nullable|date',
            'exchange_rate' => 'nullable|numeric|min:0',
        ]);

        $applications = Application::whereIn('id', $validated['application_ids'])->get();

        // Bloquear si alguna tiene cost_locked_at (costo ya inmutable).
        $locked = $applications->filter(fn ($a) => $a->cost_locked_at !== null && $a->total_cost > 0);
        if ($locked->isNotEmpty()) {
            return back()->with('error', sprintf(
                'No se puede sobrescribir el costo: %d aplicación(es) ya tienen costo bloqueado (IDs: %s).',
                $locked->count(),
                $locked->pluck('id')->implode(', ')
            ));
        }

        $updates = [
            'total_cost' => $validated['total_cost'],
            'cost_currency' => $validated['cost_currency'],
            'payment_deadline' => $validated['payment_deadline'] ?? null,
            'exchange_rate' => $validated['exchange_rate'] ?? null,
            'cost_manual_date' => now(),
            'cost_locked_at' => now(),
        ];

        DB::transaction(function () use ($applications, $updates) {
            foreach ($applications as $app) {
                $app->update($updates);
                ActivityLog::log('payment_management')
                    ->performedOn($app)
                    ->causedBy(auth()->user())
                    ->withAction('cost_bulk_assigned')
                    ->withProperties($updates)
                    ->log("Costo de programa asignado masivamente: {$updates['cost_currency']} ".number_format($updates['total_cost'], 2));
            }
        });

        return back()->with('success', "Costo asignado a {$applications->count()} aplicación(es).");
    }
}
