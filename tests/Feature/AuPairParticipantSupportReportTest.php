<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Notification;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuPairParticipantSupportReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_report_flow_for_au_pair(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        $process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'support', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'approved', 'support_status' => 'active']);

        Sanctum::actingAs($user);
        $this->postJson('/api/au-pair/support-logs', ['log_type' => 'participant_report', 'title' => 'Consulta sobre la familia', 'description' => 'Tengo dudas con los horarios', 'urgent' => false])
            ->assertCreated()->assertJsonPath('data.source', 'participant')->assertJsonPath('data.log_type_label', 'Reporte del participante');
        $this->postJson('/api/au-pair/support-logs', ['log_type' => 'monthly_followup', 'title' => 'x', 'description' => 'y'])->assertStatus(422);
        $this->getJson('/api/au-pair/support-logs')->assertOk()->assertJsonPath('data.0.source', 'participant');

        $this->assertDatabaseHas('au_pair_support_logs', ['au_pair_process_id' => $process->id, 'log_type' => 'participant_report', 'source' => 'participant']);
        $this->assertNotNull(Notification::where('user_id', $admin->id)->where('category', 'support')->first());

        $html = $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => 'support']))->assertOk()->getContent();
        $this->assertStringContainsString('Reportes del participante', $html);
        $this->assertStringContainsString('Consulta sobre la familia', $html);

        // Registro del coordinador → aviso a la participante
        $this->actingAs($admin)->post(route('admin.aupair.profiles.store-support-log', $user->id), ['log_type' => 'monthly_followup', 'title' => 'Seguimiento 1', 'log_date' => today()->toDateString()])->assertRedirect();
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'category' => 'support', 'title' => 'Nuevo seguimiento de tu coordinador']);
    }
}
