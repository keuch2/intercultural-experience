<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Au Pair: fecha de inicio/fin del programa en Datos personales, resumen lateral y API. */
class AuPairProgramDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_dates_are_editable_visible_and_exposed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        $process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'admission', 'admission_status' => 'in_progress', 'application_status' => 'locked', 'match_visa_status' => 'locked', 'support_status' => 'locked']);

        $base = ['name' => $user->name, 'enrollment_date' => '2026-02-01'];
        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-personal', $user->id), $base + ['program_start_date' => '2026-07-01', 'program_end_date' => '2027-07-01'])->assertSessionHasNoErrors();
        $this->assertSame('2026-07-01', $process->fresh()->program_start_date->toDateString());

        $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => 'admission']))->assertOk()
            ->assertSee('Inicio programa:')->assertSee('01/07/2026');

        Sanctum::actingAs($user);
        $this->getJson('/api/au-pair/process')->assertOk()
            ->assertJsonPath('data.program_start_date', '2026-07-01')->assertJsonPath('data.program_end_date', '2027-07-01');

        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-personal', $user->id), $base + ['program_start_date' => '', 'program_end_date' => ''])->assertSessionHasNoErrors();
        $this->assertNull($process->fresh()->program_start_date);

        // También desde Match / Visa J1 (mismo campo del proceso)
        $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => 'match_visa']))->assertOk()->assertSee('Fechas del programa');
        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-visa', $user->id), ['program_start_date' => '2026-08-01', 'program_end_date' => '2027-08-01'])->assertSessionHasNoErrors();
        $this->assertSame('2026-08-01', $process->fresh()->program_start_date->toDateString());
        $this->assertSame('2027-08-01', $process->fresh()->program_end_date->toDateString());
        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-visa', $user->id), ['program_start_date' => '2026-08-01', 'program_end_date' => '2026-01-01'])->assertSessionHasErrors('program_end_date');
    }

    public function test_application_notes_are_not_duplicated_in_legacy_box(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'admission', 'admission_status' => 'in_progress', 'application_status' => 'locked', 'match_visa_status' => 'locked', 'support_status' => 'locked']);
        \App\Models\ParticipantNote::create(['user_id' => $user->id, 'application_id' => $application->id, 'admin_id' => $admin->id, 'content' => 'Nota de la postulación XYZ']);

        $html = $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id]))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'Nota de la postulación XYZ'));
        $this->assertStringNotContainsString('Notas anteriores', $html);

        \App\Models\ParticipantNote::create(['user_id' => $user->id, 'application_id' => null, 'admin_id' => $admin->id, 'content' => 'Nota legacy huérfana']);
        $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id]))->assertSee('Notas anteriores')->assertSee('Nota legacy huérfana');
    }
}
