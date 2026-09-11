<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El listado de participantes muestra una etiqueta por postulación (no solo la primera),
 * coloreada según el estado de esa postulación, y ya no tiene columna Estado.
 */
class ParticipantsIndexProgramBadgesTest extends TestCase
{
    use RefreshDatabase;

    private function program(string $name, string $slug): Program
    {
        return Program::create([
            'name' => $name, 'slug' => $slug, 'description' => 'x', 'country' => 'USA',
            'main_category' => 'IE', 'subcategory' => $name, 'is_active' => true,
        ]);
    }

    public function test_lists_one_badge_per_application_with_status_color(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participant = User::factory()->create(['role' => 'user', 'name' => 'Javier Peralta']);

        $wt = $this->program('Work & Travel', 'work-travel');
        $ap = $this->program('Au Pair', 'au-pair-test');

        Application::create(['user_id' => $participant->id, 'program_id' => $wt->id, 'status' => 'approved', 'applied_at' => now()]);
        Application::create(['user_id' => $participant->id, 'program_id' => $ap->id, 'status' => 'pending', 'applied_at' => now()]);

        $res = $this->actingAs($admin)->get(route('admin.participants.index'));

        $res->assertOk()
            // Ambos programas aparecen, cada uno con el color de su propio estado
            ->assertSee('bg-success text-white mb-1', false)
            ->assertSee('Work &amp; Travel — Aprobada', false)
            ->assertSee('bg-warning text-dark mb-1', false)
            ->assertSee('Au Pair — Pendiente', false);

        // La columna Estado ya no existe como encabezado
        $this->assertStringNotContainsString('<th>Estado</th>', $res->getContent());
        $this->assertStringContainsString('<th>Programas</th>', $res->getContent());
    }

    public function test_rejected_application_uses_red_badge_and_no_applications_shows_placeholder(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rejected = User::factory()->create(['role' => 'user']);
        $none = User::factory()->create(['role' => 'user']);

        $wt = $this->program('Work & Travel', 'work-travel');
        Application::create(['user_id' => $rejected->id, 'program_id' => $wt->id, 'status' => 'rejected', 'applied_at' => now()]);

        $res = $this->actingAs($admin)->get(route('admin.participants.index'));

        $res->assertOk()
            ->assertSee('bg-danger text-white mb-1', false)
            ->assertSee('Work &amp; Travel — Rechazada', false)
            ->assertSee('Sin programa');
        $this->assertNotNull($none->id);
    }
}
