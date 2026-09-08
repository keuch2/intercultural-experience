<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\AuPairVisaProcess;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug reportado: "El campo appointment time no coincide con el formato H:i" al
 * re-guardar la etapa Match/Visa (la hora volvía como HH:MM:SS).
 */
class AuPairVisaTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_visa_appointment_time_round_trips_in_both_formats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        $process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'match_visa', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'in_progress', 'support_status' => 'locked']);

        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-visa', $user->id), ['appointment_date' => '2026-10-05', 'appointment_time' => '10:30', 'embassy' => 'Asunción'])
            ->assertSessionHasNoErrors();
        $visa = AuPairVisaProcess::where('au_pair_process_id', $process->id)->firstOrFail();
        $this->assertSame('10:30', $visa->appointment_time);
        $this->assertSame('10:30:00', $visa->getRawOriginal('appointment_time'));

        // Re-envío del formulario con el valor que devolvía la BD (con segundos)
        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-visa', $user->id), ['appointment_date' => '2026-10-05', 'appointment_time' => '10:30:00', 'embassy' => 'Asunción', 'visa_email_sent' => 1])
            ->assertSessionHasNoErrors();
        $this->assertTrue($visa->fresh()->visa_email_sent);

        $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => 'match_visa']))
            ->assertOk()->assertSee('value="10:30"', false);
    }
}
