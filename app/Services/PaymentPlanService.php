<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Currency;
use App\Models\InstallmentDetail;
use App\Models\PaymentInstallment;
use Carbon\Carbon;

/**
 * Costo del programa y plan de cuotas por postulación. Extraído de
 * AuPairProfileController (updateProgramCost / storeInstallmentPlan) para el hub
 * genérico; Au Pair conserva su copia.
 */
class PaymentPlanService
{
    public function updateProgramCost(Application $application, array $validated): Application
    {
        $application->update(array_intersect_key($validated, array_flip(['total_cost', 'cost_currency', 'exchange_rate', 'payment_deadline'])));

        return $application;
    }

    public function createInstallmentPlan(Application $application, array $validated, ?int $createdBy = null): PaymentInstallment
    {
        $total = (int) $validated['total_installments'];
        $amount = (float) $validated['total_amount'];
        $installmentAmount = round($amount / $total, 2);

        $plan = PaymentInstallment::create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'program_id' => $application->program_id,
            'plan_name' => $validated['plan_name'] ?? 'Plan de Cuotas',
            'total_installments' => $total,
            'total_amount' => $amount,
            'interest_rate' => 0,
            'currency_id' => Currency::where('code', $application->cost_currency === 'PYG' ? 'PYG' : 'USD')->first()?->id,
            'status' => 'active',
            'created_by' => $createdBy,
        ]);

        $dueDate = Carbon::parse($validated['first_due_date']);
        for ($i = 1; $i <= $total; $i++) {
            InstallmentDetail::create([
                'payment_installment_id' => $plan->id,
                'installment_number' => $i,
                'amount' => $i === $total ? round($amount - ($installmentAmount * ($total - 1)), 2) : $installmentAmount,
                'due_date' => $dueDate->copy(),
                'status' => 'pending',
                'late_fee' => 0,
            ]);
            $dueDate->addMonth();
        }

        return $plan;
    }

    /** Resumen financiero para la tab de pagos. */
    public function summary(?Application $application): array
    {
        if (! $application) {
            return ['payments' => collect(), 'total_paid' => 0.0, 'total_cost' => 0.0, 'currency' => 'USD', 'pct' => 0, 'installment_plan' => null];
        }

        $payments = $application->payments()->with(['currency', 'verifiedBy'])->orderByDesc('created_at')->get();
        $totalPaid = (float) $payments->where('status', 'verified')->sum(fn ($p) => $p->converted_amount ?? $p->amount);
        $totalCost = (float) ($application->total_cost ?? 0);

        return [
            'payments' => $payments,
            'total_paid' => $totalPaid,
            'total_cost' => $totalCost,
            'currency' => $application->cost_currency ?? 'USD',
            'pct' => $totalCost > 0 ? (int) min(100, round(($totalPaid / $totalCost) * 100)) : 0,
            'installment_plan' => PaymentInstallment::where('application_id', $application->id)->with('installmentDetails')->first(),
        ];
    }
}
