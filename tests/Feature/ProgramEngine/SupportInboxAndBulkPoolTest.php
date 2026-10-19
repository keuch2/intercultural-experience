<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\ProgramSupportLog;
use App\Support\ApplicationProgress;
use Database\Seeders\WorkTravelProgramSeeder;
use Laravel\Sanctum\Sanctum;

/** Bandeja transversal de reportes (dashboard), habilitación masiva del pool y progreso real del perfil. */
class SupportInboxAndBulkPoolTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_dashboard_lists_pending_reports_from_all_programs_and_actions_clear_them(): void
    {
        $admin = $this->admin();
        $wt = $this->processFor($this->participant(), $this->program);
        Sanctum::actingAs($wt->user);
        $this->postJson('/api/programs/work-travel/support-logs', ['log_type' => 'incident', 'title' => 'Me robaron el pasaporte', 'description' => 'Ayer', 'urgent' => true])->assertCreated();
        $this->postJson('/api/programs/work-travel/support-logs', ['log_type' => 'participant_report', 'title' => 'Consulta de horario', 'description' => 'x'])->assertCreated();

        $auPairProgram = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $apUser = $this->participant();
        $app = Application::create(['user_id' => $apUser->id, 'program_id' => $auPairProgram->id, 'status' => 'approved', 'applied_at' => now()]);
        AuPairProcess::create(['application_id' => $app->id, 'user_id' => $apUser->id, 'current_stage' => 'support', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'approved', 'support_status' => 'active']);
        Sanctum::actingAs($apUser);
        $this->postJson('/api/au-pair/support-logs', ['log_type' => 'participant_report', 'title' => 'Duda con la familia', 'description' => 'x'])->assertCreated();

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('Reportes de participantes pendientes', $html);
        $this->assertStringContainsString('Me robaron el pasaporte', $html);
        $this->assertStringContainsString('Duda con la familia', $html);

        $incident = ProgramSupportLog::where('title', 'Me robaron el pasaporte')->firstOrFail();
        $consulta = ProgramSupportLog::where('title', 'Consulta de horario')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.support-reports.read', ['program', $consulta->id]))->assertSessionHas('success');
        $this->actingAs($admin)->post(route('admin.support-reports.resolve', ['program', $incident->id]), [])->assertSessionHasErrors('resolution');
        $this->actingAs($admin)->post(route('admin.support-reports.resolve', ['program', $incident->id]), ['resolution' => 'Gestionamos el turno en el consulado'])->assertSessionHas('success');

        $this->assertSame('read', $consulta->fresh()->reviewStatus());
        $this->assertSame('resolved', $incident->fresh()->reviewStatus());
        $this->assertSame($admin->id, $incident->fresh()->resolved_by);
        $this->assertDatabaseHas('notifications', ['user_id' => $wt->user_id, 'title' => 'Tu reporte fue solucionado']);

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();
        $this->assertStringNotContainsString('Me robaron el pasaporte', $html);
        $this->assertStringContainsString('Duda con la familia', $html);

        // El historial del participante conserva ambos registros con su estado
        $tab = $this->actingAs($admin)->get(route('admin.program.participants.show', ['program' => 'work-travel', 'process' => $wt->id, 'tab' => 'support']))->getContent();
        $this->assertStringContainsString('Gestionamos el turno en el consulado', $tab);
        $this->assertStringContainsString('Leído', $tab);
    }

    public function test_bulk_pool_access_only_applies_to_eligible_selected_participants(): void
    {
        $admin = $this->admin();
        $ok1 = $this->processFor($this->participant(), $this->program);
        $ok2 = $this->processFor($this->participant(), $this->program);
        $pending = $this->processFor($this->participant(), $this->program, 'pending');
        $notSelected = $this->processFor($this->participant(), $this->program);

        $this->actingAs($admin)->get(route('admin.program.participants.index', ['program' => 'work-travel', 'pool' => 'disabled', 'per_page' => 50]))
            ->assertOk()->assertSee('Habilitar acceso al Pool');

        $this->actingAs($admin)->post(route('admin.program.participants.bulk-pool-access', 'work-travel'), [
            'process_ids' => [$ok1->id, $ok2->id, $pending->id], 'action' => 'enable',
        ])->assertSessionHas('success');

        $this->assertTrue($ok1->fresh()->hasModuleAccess('job_pool'));
        $this->assertTrue($ok2->fresh()->hasModuleAccess('job_pool'));
        $this->assertFalse($pending->fresh()->hasModuleAccess('job_pool'), 'postulación no aprobada: se omite');
        $this->assertFalse($notSelected->fresh()->hasModuleAccess('job_pool'));
        $this->assertDatabaseHas('notifications', ['user_id' => $ok1->user_id, 'title' => 'Pool de Ofertas habilitado']);

        $this->actingAs($admin)->post(route('admin.program.participants.bulk-pool-access', 'work-travel'), ['process_ids' => [$ok2->id], 'action' => 'disable'])->assertSessionHas('success');
        $this->assertFalse($ok2->fresh()->hasModuleAccess('job_pool'));
    }

    public function test_profile_progress_comes_from_the_real_process(): void
    {
        $process = $this->processFor($this->participant(), $this->program);
        $process->application->update(['current_stage' => 'registration', 'progress_percentage' => 5]);
        $process->update(['current_stage_key' => 'application']);

        $p = ApplicationProgress::for($process->application->fresh('program'));
        $this->assertSame('application', $p['key']);
        $this->assertSame('Aplicación', $p['label']);
        $this->assertGreaterThan(5, $p['percent']);

        Sanctum::actingAs($process->user);
        $this->getJson('/api/applications')->assertOk()->assertJsonPath('data.0.current_stage', 'application');
    }
}
