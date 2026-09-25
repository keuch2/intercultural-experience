<?php

namespace Tests\Feature\ProgramEngine;

use App\Models\Notification;
use App\Models\Program;
use Database\Seeders\WorkTravelProgramSeeder;
use Laravel\Sanctum\Sanctum;

/** El participante envía reportes / incidentes desde la app; IE recibe aviso y los ve en el hub. */
class ParticipantSupportReportTest extends EngineTestCase
{
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkTravelProgramSeeder::class);
        $this->program = Program::where('slug', 'work-travel')->firstOrFail();
    }

    public function test_participant_creates_report_admins_notified_and_hub_shows_it_even_without_rule(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        // Reglas del programa sin 'participant_report': igual debe aceptarse y verse
        $this->program->update(['rules' => array_merge((array) $this->program->rules, ['support_log_types' => ['arrival_followup', 'incident']])]);
        \App\Services\ProgramEngine\ProgramDefinition::forget();

        Sanctum::actingAs($process->user);
        $this->postJson('/api/programs/work-travel/support-logs', ['log_type' => 'incident', 'title' => 'Problema con el empleador', 'description' => 'No me pagaron la primera semana', 'urgent' => true])
            ->assertCreated()->assertJsonPath('data.source', 'participant')->assertJsonPath('data.severity', 'high')->assertJsonPath('data.log_type', 'incident');
        $this->postJson('/api/programs/work-travel/support-logs', ['log_type' => 'participant_report', 'title' => 'Consulta de horario', 'description' => 'Quería saber si puedo cambiar el turno'])
            ->assertCreated()->assertJsonPath('data.log_type_label', 'Reporte / consulta')->assertJsonPath('data.severity', null);
        $this->postJson('/api/programs/work-travel/support-logs', ['log_type' => 'arrival_followup', 'title' => 'x', 'description' => 'y'])->assertStatus(422);

        $this->assertDatabaseHas('program_support_logs', ['program_process_id' => $process->id, 'source' => 'participant', 'log_type' => 'incident', 'severity' => 'high', 'logged_by' => $process->user_id]);
        $alert = Notification::where('user_id', $admin->id)->where('category', 'support')->first();
        $this->assertNotNull($alert);
        $this->assertStringStartsWith('Nuevo reporte del participante', $alert->title);
        $this->assertStringContainsString('URGENTE', $alert->message);

        $this->getJson('/api/programs/work-travel/support-logs')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.source', 'participant');

        $html = $this->actingAs($admin)->get(route('admin.program.participants.show', ['program' => 'work-travel', 'process' => $process->id, 'tab' => 'support']))->assertOk()->getContent();
        $this->assertStringContainsString('Consulta de horario', $html);
        $this->assertStringContainsString('Reportado por el participante', $html);
        $this->assertStringContainsString('Reportes del participante', $html);
        $this->assertMatchesRegularExpression('/Support<\/span>\s*<span class="ms-auto badge bg-warning text-dark"[^>]*>2</', $html);
    }

    public function test_staff_log_notifies_participant(): void
    {
        $admin = $this->admin();
        $process = $this->processFor($this->participant(), $this->program);
        $this->actingAs($admin)->post(route('admin.program.support.store', ['program' => 'work-travel', 'process' => $process->id]), [
            'log_type' => 'program_followup', 'title' => 'Llamada mensual', 'log_date' => today()->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseHas('program_support_logs', ['program_process_id' => $process->id, 'source' => 'staff']);
        $this->assertDatabaseHas('notifications', ['user_id' => $process->user_id, 'category' => 'support', 'title' => 'Nuevo seguimiento de tu coordinador']);
    }
}
