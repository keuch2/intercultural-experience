<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Au Pair: desde Match/Visa se pasa a Support; la finalización vive en el tab Support. */
class AuPairFinalizationFromSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_match_visa_advances_to_support_and_finalization_lives_in_support_tab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        $process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'match_visa', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'in_progress', 'support_status' => 'locked']);

        $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => 'match_visa']))->assertOk()
            ->assertSee('Siguiente etapa: Support')->assertDontSee('Finalización del Programa');

        $this->actingAs($admin)->post(route('admin.aupair.profiles.advance-stage', $user->id))->assertRedirect();
        $this->assertSame('support', $process->fresh()->current_stage);
        $this->assertSame('active', $process->fresh()->support_status);

        $this->actingAs($admin)->get(route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => 'support']))->assertOk()
            ->assertSee('Finalización del Programa');

        $this->actingAs($admin)->put(route('admin.aupair.profiles.update-finalization', $user->id), ['finalization_result' => 'success'])
            ->assertRedirect(route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => 'support']));
        $fresh = $process->fresh();
        $this->assertSame('completed', $fresh->current_stage);
        $this->assertSame('completed', $fresh->support_status);
    }
}
