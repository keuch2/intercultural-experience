<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use App\Services\ApplicationReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Una solicitud rechazada no es una postulación en curso: el Perfil General no la muestra como
 * tarjeta de aplicación ni como programa del participante, solo como historial.
 */
class ParticipantProfileRejectedApplicationsTest extends TestCase
{
    use RefreshDatabase;

    private function program(string $name, string $slug): Program
    {
        return Program::create([
            'name' => $name, 'slug' => $slug, 'description' => 'x', 'country' => 'USA',
            'main_category' => 'IE', 'subcategory' => $name, 'is_active' => true,
        ]);
    }

    public function test_rejected_request_is_listed_as_history_not_as_application(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = User::factory()->create(['role' => 'user']);
        $program = $this->program('Programa Rechazado QA', 'programa-rechazado-qa');

        $application = Application::create([
            'user_id' => $participant->id, 'program_id' => $program->id, 'status' => 'pending', 'applied_at' => now(),
        ]);
        app(ApplicationReviewService::class)->reject($application, $admin, 'Cupo completo');

        $html = $this->actingAs($admin)->get(route('admin.participants.show', $participant))->assertOk()->getContent();

        // No aparece como tarjeta de postulación ni como programa del participante.
        $this->assertStringNotContainsString("Solicitud #{$application->id}", $html);
        $this->assertStringContainsString('No hay aplicaciones en curso para este participante.', $html);
        $this->assertStringContainsString('Sin aplicación', $html);
        $this->assertStringContainsString('Sin programa', $html);

        // Queda en el historial de solicitudes no aprobadas, con el motivo.
        $this->assertStringContainsString('Solicitudes no aprobadas', $html);
        $this->assertStringContainsString('Programa Rechazado QA', $html);
        $this->assertStringContainsString('Cupo completo', $html);
    }

    public function test_active_application_still_shows_as_card_next_to_rejected_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = User::factory()->create(['role' => 'user']);
        $rejectedProgram = $this->program('Programa Rechazado QA', 'programa-rechazado-qa');
        $activeProgram = $this->program('Programa Activo QA', 'programa-activo-qa');

        $rejected = Application::create([
            'user_id' => $participant->id, 'program_id' => $rejectedProgram->id, 'status' => 'pending', 'applied_at' => now(),
        ]);
        app(ApplicationReviewService::class)->reject($rejected, $admin, null);
        $active = Application::create([
            'user_id' => $participant->id, 'program_id' => $activeProgram->id, 'status' => 'pending', 'applied_at' => now(),
        ]);

        $html = $this->actingAs($admin)->get(route('admin.participants.show', $participant))->assertOk()->getContent();

        $this->assertStringContainsString("Solicitud #{$active->id}", $html);
        $this->assertStringNotContainsString("Solicitud #{$rejected->id}", $html);
        $this->assertStringContainsString('Solicitudes no aprobadas', $html);
    }
}
