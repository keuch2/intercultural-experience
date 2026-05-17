<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Endpoints de pagos para mobile (V1 Au Pair).
 * Todos scoped al `auth()->user()`: el participante ve y crea solo
 * sus propios pagos. La verificación la hace el admin desde web.
 */
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $appId = $request->query('application_id');

        $query = Payment::where('user_id', $user->id);
        if ($appId) {
            $this->assertOwnsApplication($user->id, (int) $appId);
            $query->where('application_id', $appId);
        }

        $payments = $query->orderByDesc('payment_date')->orderByDesc('id')->get();
        return response()->json([
            'status' => 'success',
            'data' => $payments->map(fn ($p) => $this->serialize($p))->values(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        $payment = Payment::where('user_id', $request->user()->id)->find($id);
        if (! $payment) {
            return response()->json(['status' => 'error', 'message' => 'Pago no encontrado.'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $this->serialize($payment)]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'application_id' => 'required|integer|exists:applications,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'concept' => 'required|string|max:120',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:80',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'receipt' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240', // 10 MB
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $this->assertOwnsApplication($user->id, (int) $request->input('application_id'));

        $application = Application::find($request->input('application_id'));
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store(
                "payments/{$user->id}",
                'public'
            );
        }

        $payment = Payment::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'program_id' => $application->program_id,
            'currency_id' => $request->input('currency_id') ?: Currency::query()->where('code', 'USD')->value('id'),
            'amount' => $request->input('amount'),
            'payment_method' => $request->input('payment_method'),
            'concept' => $request->input('concept'),
            'reference_number' => $request->input('reference_number'),
            'payment_date' => $request->input('payment_date') ?? now()->toDateString(),
            'status' => 'pending',
            'notes' => $request->input('notes'),
            'receipt_path' => $receiptPath,
            'created_by' => $user->id,
        ]);

        return response()->json(['status' => 'success', 'data' => $this->serialize($payment)], 201);
    }

    public function uploadReceipt(Request $request, string $id)
    {
        $payment = Payment::where('user_id', $request->user()->id)->find($id);
        if (! $payment) {
            return response()->json(['status' => 'error', 'message' => 'Pago no encontrado.'], 404);
        }
        if ($payment->status === 'verified') {
            return response()->json(['status' => 'error', 'message' => 'Este pago ya fue verificado y no acepta cambios.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'receipt' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // Eliminar receipt previo si existía
        if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
            Storage::disk('public')->delete($payment->receipt_path);
        }

        $payment->receipt_path = $request->file('receipt')->store(
            "payments/{$request->user()->id}",
            'public'
        );
        $payment->save();

        return response()->json(['status' => 'success', 'data' => $this->serialize($payment)]);
    }

    public function installments(Request $request)
    {
        $user = $request->user();
        $appId = (int) $request->query('application_id');
        if (! $appId) {
            return response()->json(['status' => 'error', 'message' => 'application_id requerido.'], 422);
        }
        $this->assertOwnsApplication($user->id, $appId);

        $plan = PaymentInstallment::where('application_id', $appId)
            ->where('user_id', $user->id)
            ->with('installmentDetails')
            ->latest('id')
            ->first();

        if (! $plan) {
            return response()->json(['status' => 'success', 'data' => null]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $plan->id,
                'plan_name' => $plan->plan_name,
                'total_installments' => $plan->total_installments,
                'total_amount' => $plan->total_amount,
                'currency' => optional($plan->currency)->code,
                'status' => $plan->status,
                'details' => $plan->installmentDetails->map(fn ($d) => [
                    'id' => $d->id,
                    'installment_number' => $d->installment_number,
                    'amount' => $d->amount,
                    'due_date' => optional($d->due_date)->toDateString(),
                    'status' => $d->status,
                ])->values(),
            ],
        ]);
    }

    private function assertOwnsApplication(int $userId, int $applicationId): void
    {
        $ok = Application::where('id', $applicationId)->where('user_id', $userId)->exists();
        if (! $ok) {
            abort(response()->json(['status' => 'error', 'message' => 'No autorizado para esta aplicación.'], 403));
        }
    }

    private function serialize(Payment $p): array
    {
        $receiptUrl = $p->receipt_path
            ? asset('storage/' . $p->receipt_path)
            : null;

        return [
            'id' => $p->id,
            'application_id' => $p->application_id,
            'concept' => $p->concept,
            'amount' => (float) $p->amount,
            'currency' => optional($p->currency)->code,
            'exchange_rate' => $p->exchange_rate ? (float) $p->exchange_rate : null,
            'converted_amount' => $p->converted_amount ? (float) $p->converted_amount : null,
            'payment_method' => $p->payment_method,
            'reference_number' => $p->reference_number,
            'payment_date' => optional($p->payment_date)->toDateString(),
            'status' => $p->status,
            'status_label' => $p->status_label,
            'notes' => $p->notes,
            'receipt_url' => $receiptUrl,
            'verified_at' => optional($p->verified_at)->toIso8601String(),
            'created_at' => optional($p->created_at)->toIso8601String(),
        ];
    }
}
