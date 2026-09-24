<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuPairFlashOnceTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_message_is_rendered_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['name' => 'Au Pair USA', 'description' => 'x', 'country' => 'USA', 'main_category' => 'IE', 'subcategory' => 'Au Pair', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user']);
        $application = Application::create(['user_id' => $user->id, 'program_id' => $program->id, 'status' => 'approved', 'applied_at' => now()]);
        AuPairProcess::create(['application_id' => $application->id, 'user_id' => $user->id, 'current_stage' => 'admission', 'admission_status' => 'in_progress', 'application_status' => 'locked', 'match_visa_status' => 'locked', 'support_status' => 'locked']);

        $html = $this->actingAs($admin)->withSession(['success' => 'Documento eliminado.'])->get(route('admin.aupair.profiles.show', ['id' => $user->id]))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'Documento eliminado.'));
    }
}
