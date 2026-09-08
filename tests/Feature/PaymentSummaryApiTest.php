<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Currency;
use App\Models\InstallmentDetail;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    private function application(User $user): Application
    {
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);

        return Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now(), 'total_cost' => 1100, 'cost_currency' => 'USD', 'payment_deadline' => '2026-12-31']);
    }

    private function payment(Application $app, string $status, float $amount, ?float $converted = null): Payment
    {
        return Payment::create([
            'application_id' => $app->id, 'user_id' => $app->user_id, 'program_id' => $app->program_id,
            'currency_id' => Currency::firstOrCreate(['code' => 'USD'], ['name' => 'Dólar', 'symbol' => '$', 'exchange_rate_to_pyg' => 1, 'is_active' => true])->id,
            'amount' => $amount, 'converted_amount' => $converted, 'concept' => 'Inscripción', 'payment_date' => now()->toDateString(), 'status' => $status,
        ]);
    }

    public function test_summary_matches_admin_formula_verified_only(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $app = $this->application($user);
        $this->payment($app, 'verified', 200);
        $this->payment($app, 'verified', 8000, 100); // pago en PYG convertido a 100 USD
        $this->payment($app, 'pending', 300);
        $this->payment($app, 'rejected', 500);

        $plan = PaymentInstallment::create(['application_id' => $app->id, 'user_id' => $user->id, 'program_id' => $app->program_id, 'plan_name' => 'Plan', 'total_installments' => 2, 'total_amount' => 800, 'interest_rate' => 0, 'status' => 'active', 'created_by' => $user->id]);
        InstallmentDetail::create(['payment_installment_id' => $plan->id, 'installment_number' => 1, 'amount' => 400, 'due_date' => '2026-10-01', 'status' => 'paid', 'late_fee' => 0]);
        InstallmentDetail::create(['payment_installment_id' => $plan->id, 'installment_number' => 2, 'amount' => 400, 'due_date' => '2026-11-01', 'status' => 'pending', 'late_fee' => 0]);
        PaymentInstallment::create(['application_id' => $app->id, 'user_id' => $user->id, 'program_id' => $app->program_id, 'plan_name' => 'Viejo', 'total_installments' => 3, 'total_amount' => 999, 'interest_rate' => 0, 'status' => 'cancelled', 'created_by' => $user->id]);

        Sanctum::actingAs($user);
        $this->getJson("/api/payments/summary?application_id={$app->id}")->assertOk()
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.total_cost', 1100)
            ->assertJsonPath('data.amount_paid', 300)
            ->assertJsonPath('data.pending_amount', 300)
            ->assertJsonPath('data.balance', 800)
            ->assertJsonPath('data.progress_pct', 27)
            ->assertJsonPath('data.payment_deadline', '2026-12-31')
            ->assertJsonPath('data.verified_count', 2)
            ->assertJsonPath('data.installment_plan.plan_name', 'Plan')
            ->assertJsonPath('data.installment_plan.paid_installments', 1)
            ->assertJsonPath('data.installment_plan.next_due_date', '2026-11-01');

        // installments tampoco devuelve el plan cancelado
        $this->getJson("/api/payments/installments?application_id={$app->id}")->assertOk()->assertJsonPath('data.plan_name', 'Plan');
    }

    public function test_summary_without_cost_and_ownership(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $app = $this->application($user);
        $app->update(['total_cost' => null]);
        $this->payment($app, 'verified', 50);

        Sanctum::actingAs($user);
        $this->getJson("/api/payments/summary?application_id={$app->id}")->assertOk()
            ->assertJsonPath('data.total_cost', 0)->assertJsonPath('data.amount_paid', 50)->assertJsonPath('data.balance', 0)->assertJsonPath('data.installment_plan', null);
        $this->getJson('/api/payments/summary')->assertStatus(422);

        Sanctum::actingAs(User::factory()->create(['role' => 'user']));
        $this->getJson("/api/payments/summary?application_id={$app->id}")->assertStatus(403);
    }
}
