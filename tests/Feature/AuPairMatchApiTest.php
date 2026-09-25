<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairMatchExtended;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuPairMatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_payload_exposes_active_match(): void
    {
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        $process = AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'match_visa', 'admission_status' => 'approved', 'application_status' => 'approved', 'match_visa_status' => 'in_progress', 'support_status' => 'locked']);

        Sanctum::actingAs($user);
        $this->getJson('/api/au-pair/process')->assertOk()->assertJsonPath('data.active_match', null)->assertJsonPath('data.matches_count', 0);

        AuPairMatchExtended::create(['au_pair_process_id' => $process->id, 'match_type' => 'initial', 'match_date' => '2026-03-01', 'host_state' => 'Texas', 'host_city' => 'Austin', 'is_active' => false, 'ended_at' => '2026-05-01', 'sort_order' => 1]);
        $active = AuPairMatchExtended::create(['au_pair_process_id' => $process->id, 'match_type' => 'rematch', 'match_date' => '2026-05-02', 'host_state' => 'Florida', 'host_city' => 'Miami', 'is_active' => true, 'sort_order' => 2]);

        $this->getJson('/api/au-pair/process')->assertOk()
            ->assertJsonPath('data.matches_count', 2)
            ->assertJsonPath('data.active_match.id', $active->id)
            ->assertJsonPath('data.active_match.host_city', 'Miami')
            ->assertJsonPath('data.active_match.match_date', '2026-05-02');
        $this->getJson('/api/au-pair/matches')->assertOk()->assertJsonCount(2, 'data');
    }
}
