<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use App\Models\InstallmentDetail;
use App\Models\Currency;
use App\Models\Program;
use Illuminate\Http\Request;
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
            ->with(['user', 'program'])
            ->whereNotNull('total_cost')
            ->where('total_cost', '>', 0);

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
}
