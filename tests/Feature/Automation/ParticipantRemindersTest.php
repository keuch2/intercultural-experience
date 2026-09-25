<?php

namespace Tests\Feature\Automation;

use App\Models\Currency;
use App\Models\InstallmentDetail;
use App\Models\Notification;
use App\Models\PaymentInstallment;
use App\Models\Program;
use App\Models\ProgramProcess;
use Database\Seeders\WorkTravelProgramSeeder;
use Tests\Feature\ProgramEngine\EngineTestCase;

/** participants:send-reminders — cuotas, fecha límite, documentos y cita de visa, sin duplicados. */
class ParticipantRemindersTest extends EngineTestCase
{
    private Program $program;

    private string $today = '2026-06-15';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    private function runReminders(?string $date = null, bool $dryRun = false): void
    {
        $args = ['--date' => $date ?? $this->today];
        if ($dryRun) {
            $args['--dry-run'] = true;
        }
        $this->artisan('participants:send-reminders', $args)->assertSuccessful();
    }

    private function approvedProcess(): ProgramProcess
    {
        return $this->processFor($this->participant(), $this->program, 'approved');
    }

    private function plan(ProgramProcess $process, array $dueDates): PaymentInstallment
    {
        $usd = Currency::firstOrCreate(['code' => 'USD'], ['name' => 'Dólar', 'symbol' => '$', 'exchange_rate_to_pyg' => 1, 'is_active' => true]);
        $plan = PaymentInstallment::create(['application_id' => $process->application_id, 'user_id' => $process->user_id, 'program_id' => $process->program_id, 'plan_name' => 'Plan', 'total_installments' => count($dueDates), 'total_amount' => 100 * count($dueDates), 'currency_id' => $usd->id, 'status' => 'active', 'created_by' => $this->admin()->id]);
        foreach ($dueDates as $i => $due) {
            InstallmentDetail::create(['payment_installment_id' => $plan->id, 'installment_number' => $i + 1, 'amount' => 100, 'due_date' => $due, 'status' => 'pending']);
        }

        return $plan;
    }

    private function participantNotices(ProgramProcess $process, string $category)
    {
        return Notification::where('user_id', $process->user_id)->where('category', $category)->orderBy('id')->get();
    }

    public function test_installments_due_7_3_1_and_overdue_once(): void
    {
        $process = $this->approvedProcess();
        $this->plan($process, ['2026-06-22', '2026-06-18', '2026-06-16', '2026-06-10']); // +7, +3, +1, vencida

        $this->runReminders();
        $titles = $this->participantNotices($process, 'payment')->pluck('title')->all();
        $this->assertContains('Cuota vence en 7 día(s)', $titles);
        $this->assertContains('Cuota vence en 3 día(s)', $titles);
        $this->assertContains('Cuota vence en 1 día(s)', $titles);
        $this->assertContains('Cuota vencida', $titles);
        $this->assertDatabaseHas('installment_details', ['due_date' => '2026-06-10', 'status' => 'overdue']);

        $count = Notification::count();
        $this->runReminders();
        $this->assertSame($count, Notification::count(), 'segunda corrida sin duplicados');
    }

    public function test_docs_reminder_weekly_and_visa_reminders(): void
    {
        $process = $this->approvedProcess(); // admisión: cédula requerida faltante
        $process->visaProcess()->create(['appointment_date' => '2026-06-18', 'appointment_time' => '10:30']); // +3

        $this->runReminders();
        $docs = $this->participantNotices($process, 'documents');
        $this->assertCount(1, $docs);
        $this->assertStringContainsString('Cédula de Identidad', $docs[0]->message);
        $this->assertSame('Tu cita de visa es en 3 día(s)', $this->participantNotices($process, 'visa')->first()->title);

        $this->runReminders('2026-06-18'); // 3 días después: docs no se repite; visa no (0 días)
        $this->assertCount(1, $this->participantNotices($process, 'documents'));
        $this->runReminders('2026-06-22'); // 7 días después: se repite el recordatorio de documentos
        $this->assertCount(2, $this->participantNotices($process, 'documents'));
    }

    public function test_dry_run_writes_nothing(): void
    {
        $process = $this->approvedProcess();
        $this->plan($process, ['2026-06-16']);
        $this->artisan('participants:send-reminders', ['--date' => $this->today, '--dry-run' => true])->expectsOutputToContain('installment_due')->assertSuccessful();
        $this->assertSame(0, Notification::count());
        $this->assertDatabaseCount('participant_reminders', 0);
    }
}
