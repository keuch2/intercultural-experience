<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests unitarios para funcionalidad de agentes en User model
 */
class UserModelAgentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function is_agent_returns_true_for_agent_role()
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $this->assertTrue($agent->isAgent());
    }

    /** @test */
    public function is_agent_returns_false_for_non_agent_roles()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($admin->isAgent());
        $this->assertFalse($user->isAgent());
    }

    /** @test */
    public function created_by_agent_relationship_returns_correct_agent()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $participant = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $agent->id,
        ]);

        $this->assertInstanceOf(User::class, $participant->createdByAgent);
        $this->assertEquals($agent->id, $participant->createdByAgent->id);
    }

    /** @test */
    public function created_by_agent_returns_null_when_not_created_by_agent()
    {
        $participant = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => null,
        ]);

        $this->assertNull($participant->createdByAgent);
    }

    /** @test */
    public function created_participants_returns_only_users_created_by_agent()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        $participant1 = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $agent->id,
        ]);
        
        $participant2 = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $agent->id,
        ]);
        
        // Participante de otro agente
        $otherAgent = User::factory()->create(['role' => 'agent']);
        $participant3 = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $otherAgent->id,
        ]);

        $createdParticipants = $agent->createdParticipants;

        $this->assertEquals(2, $createdParticipants->count());
        $this->assertTrue($createdParticipants->contains($participant1));
        $this->assertTrue($createdParticipants->contains($participant2));
        $this->assertFalse($createdParticipants->contains($participant3));
    }

    /** @test */
    public function created_by_agent_id_is_fillable()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        $participant = User::create([
            'name' => 'Test Participant',
            'email' => 'test@participant.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'created_by_agent_id' => $agent->id,
            'phone' => '+123456789',
            'country' => 'Test Country',
        ]);

        $this->assertEquals($agent->id, $participant->created_by_agent_id);
    }
}
