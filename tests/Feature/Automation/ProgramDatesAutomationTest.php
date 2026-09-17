<?php

namespace Tests\Feature\Automation;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Notification;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\ProgramDefinition;
use Database\Seeders\WorkTravelProgramSeeder;
use Tests\Feature\ProgramEngine\EngineTestCase;

/**
 * programs:process-dates — alertas a IE 7 días antes del inicio/fin, el día del fin,
 * y paso automático a Support el día de inicio solo si el participante está listo.
 */
class ProgramDatesAutomationTest extends EngineTestCase
{
    private Program $program;

    private string $today = '2026-06-15';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
        $this->admin(); // destinatario de las alertas
    }

    private function runAutomation(?string $date = null, bool $dryRun = false): void
    {
        $args = ['--date' => $date ?? $this->today];
        if ($dryRun) {
            $args['--dry-run'] = true;
        }
        $this->artisan('programs:process-dates', $args)->assertSuccessful();
    }

    private function atVisa(array $attrs = []): ProgramProcess
    {
        $process = $this->processFor($this->participant(), $this->program);
        $process->update(['current_stage_key' => 'visa'] + $attrs);

        return $process->fresh();
    }

    private function readyForSupport(ProgramProcess $process): void
    {
        $process->visaProcess()->firstOrCreate([])->update(['interview_result' => 'approved']);
        foreach (ProgramDefinition::for($this->program)->requirements('visa')->where('is_required', true) as $req) {
            $this->approvedDoc($process, $req->key, 'visa', max(1, (int) $req->min_count));
        }
    }

    private function adminAlerts(): \Illuminate\Support\Collection
    {
        return Notification::where('category', 'program_dates')->get();
    }

    public function test_engine_advances_to_support_on_start_date_when_ready(): void
    {
        $process = $this->atVisa(['program_start_date' => $this->today]);
        $this->readyForSupport($process);

        $this->runAutomation();

        $fresh = $process->fresh();
        $this->assertSame('support', $fresh->current_stage_key);
        $this->assertSame('in_progress', $fresh->stageStatus('support'));
        $this->assertNotNull($fresh->automationFlag('auto_advanced_at'));
        $this->assertNotNull($fresh->automationFlag('started_handled_at'));
        $this->assertDatabaseHas('notifications', ['user_id' => $process->user_id, 'category' => 'program_stage']);
        $this->assertStringStartsWith('Avance automático a Support', $this->adminAlerts()->first()->title);
        $this->assertDatabaseHas('activity_logs', ['log_name' => 'work-travel', 'action' => 'stage_advanced_auto']);

        // Segunda corrida: nada nuevo
        $before = Notification::count();
        $this->runAutomation();
        $this->assertSame($before, Notification::count());
    }

    public function test_engine_does_not_move_when_requirements_pending_and_alerts_ie(): void
    {
        $process = $this->atVisa(['program_start_date' => $this->today]);

        $this->runAutomation();

        $this->assertSame('visa', $process->fresh()->current_stage_key);
        $alert = $this->adminAlerts()->first();
        $this->assertStringStartsWith('Inicio de programa sin avance', $alert->title);
        $this->assertStringContainsString('Visa aún no aprobada.', $alert->message);
        $this->assertDatabaseMissing('notifications', ['user_id' => $process->user_id, 'category' => 'program_stage']);
        $this->assertNotNull($process->fresh()->automationFlag('started_handled_at'));
    }

    public function test_engine_alerts_before_start_and_around_end(): void
    {
        $soon = $this->atVisa(['program_start_date' => '2026-06-22']);          // +7
        $endSoon = $this->atVisa(['program_end_date' => '2026-06-20']);         // +5
        $endToday = $this->atVisa(['program_end_date' => $this->today]);

        $this->runAutomation();

        $titles = $this->adminAlerts()->pluck('title');
        $this->assertTrue($titles->contains(fn ($t) => str_starts_with($t, 'Inicio de programa en 7 días')));
        $this->assertTrue($titles->contains(fn ($t) => str_starts_with($t, 'Fin de programa en 5 días')));
        $this->assertTrue($titles->contains(fn ($t) => str_starts_with($t, 'Fin de programa:')));
        $this->assertNotNull($soon->fresh()->automationFlag('start_alert_sent_at'));
        $this->assertNotNull($endSoon->fresh()->automationFlag('end_alert_sent_at'));
        $this->assertNotNull($endToday->fresh()->automationFlag('ended_alert_sent_at'));
        $this->assertSame('visa', $endToday->fresh()->current_stage_key, 'el fin no finaliza automáticamente');

        $count = Notification::count();
        $this->runAutomation();
        $this->assertSame($count, Notification::count());
    }

    public function test_process_not_on_previous_stage_or_program_without_support_is_not_moved(): void
    {
        $early = $this->processFor($this->participant(), $this->program);
        $early->update(['current_stage_key' => 'job_pool', 'program_start_date' => $this->today]);

        $plain = $this->engineProgram(); // 3 etapas, sin Support
        $noSupport = $this->processFor($this->participant(), $plain);
        $noSupport->update(['current_stage_key' => 'application', 'program_start_date' => $this->today]);

        $this->runAutomation();

        $this->assertSame('job_pool', $early->fresh()->current_stage_key);
        $this->assertSame('application', $noSupport->fresh()->current_stage_key);
        $messages = $this->adminAlerts()->pluck('message')->implode(' | ');
        $this->assertStringContainsString('no en "Gestión de Visa J1"', $messages);
        $this->assertStringContainsString('no tiene etapa Support configurada', $messages);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $process = $this->atVisa(['program_start_date' => $this->today]);
        $this->readyForSupport($process);

        $this->artisan('programs:process-dates', ['--date' => $this->today, '--dry-run' => true])
            ->expectsOutputToContain('auto_advanced')->assertSuccessful();

        $this->assertSame('visa', $process->fresh()->current_stage_key);
        $this->assertNull($process->fresh()->automation_flags);
        $this->assertSame(0, Notification::count());
        $this->assertDatabaseMissing('activity_logs', ['action' => 'stage_advanced_auto']);
    }

    // ── Au Pair ─────────────────────────────────────────────────────────

    private function auPairProcess(array $attrs = []): AuPairProcess
    {
        $auPair = Program::firstOrCreate(['slug' => 'au-pair-usa'], ['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = $this->participant();
        $application = Application::create(['user_id' => $user->id, 'program_id' => $auPair->id, 'status' => 'approved', 'applied_at' => now()]);

        return AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'match_visa', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'in_progress', 'support_status' => 'locked'] + $attrs);
    }

    public function test_au_pair_advances_when_visa_approved_and_alerts_when_not(): void
    {
        $ready = $this->auPairProcess(['program_start_date' => $this->today]);
        $ready->visaProcess()->create(['interview_result' => 'approved']);
        $blocked = $this->auPairProcess(['program_start_date' => $this->today]);
        $soon = $this->auPairProcess(['program_start_date' => '2026-06-20', 'program_end_date' => '2026-06-15']);

        $this->runAutomation();

        $this->assertSame('support', $ready->fresh()->current_stage);
        $this->assertSame('active', $ready->fresh()->support_status);
        $this->assertDatabaseHas('notifications', ['user_id' => $ready->user_id, 'category' => 'program_stage', 'title' => 'Tu proceso avanzó: Support']);
        $this->assertDatabaseHas('activity_logs', ['log_name' => 'au_pair', 'action' => 'stage_advanced_auto']);

        $this->assertSame('match_visa', $blocked->fresh()->current_stage);
        $messages = $this->adminAlerts()->pluck('message')->implode(' | ');
        $this->assertStringContainsString('Visa aún no aprobada', $messages);
        $this->assertStringContainsString('inicia el programa el 20/06/2026', $messages);
        $this->assertStringContainsString('terminó el 15/06/2026', $messages);
        $this->assertNotNull($soon->fresh()->automationFlag('start_alert_sent_at'));

        $count = Notification::count();
        $this->runAutomation();
        $this->assertSame($count, Notification::count());
    }
}
