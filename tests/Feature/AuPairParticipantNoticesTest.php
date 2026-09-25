<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairDocument;
use App\Models\AuPairProcess;
use App\Models\Notification;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuPairParticipantNoticesTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage_advance_document_review_and_visa_notify_participant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        $process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'match_visa', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'in_progress', 'support_status' => 'locked']);

        // Documento rechazado con motivo
        $doc = AuPairDocument::create(['au_pair_process_id' => $process->id, 'document_type' => 'cedula', 'stage' => 'admission', 'file_path' => 'x.jpg', 'original_filename' => 'x.jpg', 'file_size' => 1, 'status' => 'pending', 'min_count' => 1]);
        $this->actingAs($admin)->put(route('admin.aupair.profiles.review-doc', [$user->id, $doc->id]), ['action' => 'reject', 'rejection_reason' => 'Vencida'])->assertRedirect();
        $n = Notification::where('user_id', $user->id)->where('category', 'documents')->first();
        $this->assertSame('Documento rechazado: Cédula de Identidad', $n->title);
        $this->assertStringContainsString('Vencida', $n->message);

        // Cita de visa agendada
        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-visa', $user->id), ['appointment_date' => '2026-10-05', 'appointment_time' => '09:00', 'embassy' => 'Asunción'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'category' => 'visa', 'title' => 'Cita de visa agendada']);
        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-visa', $user->id), ['appointment_date' => '2026-10-05', 'appointment_time' => '09:00', 'embassy' => 'Asunción'])->assertSessionHasNoErrors();
        $this->assertSame(1, Notification::where('user_id', $user->id)->where('category', 'visa')->count());

        // Avance manual (forzado) → aviso de etapa
        $this->actingAs($admin)->post(route('admin.aupair.profiles.advance-stage', $user->id), ['force' => 1])->assertRedirect();
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'category' => 'program_stage', 'title' => 'Tu proceso avanzó: Support']);
    }
}
