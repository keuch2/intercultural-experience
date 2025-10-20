<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Program;
use App\Models\ProgramAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Tests para el Sistema de Roles de Agentes
 * Épica 1 - Sprint 1
 */
class AgentSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $agent;
    protected $otherAgent;
    protected $participant;
    protected $program;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        // Crear agente
        $this->agent = User::factory()->create([
            'role' => 'agent',
            'email' => 'agent@test.com',
            'name' => 'Test Agent',
        ]);

        // Crear otro agente
        $this->otherAgent = User::factory()->create([
            'role' => 'agent',
            'email' => 'agent2@test.com',
        ]);

        // Crear participante creado por el agente
        $this->participant = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $this->agent->id,
        ]);

        // Crear programa con cupos
        $this->program = Program::factory()->create([
            'status' => 'active',
            'available_slots' => 10,
        ]);
    }

    /** @test */
    public function agent_can_access_agent_dashboard()
    {
        $response = $this->actingAs($this->agent)
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('agent.dashboard');
        $response->assertSee('Dashboard del Agente');
    }

    /** @test */
    public function admin_cannot_access_agent_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('agent.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function agent_can_see_only_their_participants()
    {
        $response = $this->actingAs($this->agent)
            ->get(route('agent.participants.index'));

        $response->assertStatus(200);
        $response->assertSee($this->participant->name);
    }

    /** @test */
    public function agent_cannot_see_other_agents_participants()
    {
        $otherParticipant = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $this->otherAgent->id,
            'name' => 'Other Participant',
        ]);

        $response = $this->actingAs($this->agent)
            ->get(route('agent.participants.index'));

        $response->assertStatus(200);
        $response->assertDontSee($otherParticipant->name);
    }

    /** @test */
    public function agent_can_create_participant()
    {
        $participantData = [
            'name' => 'New Participant',
            'email' => 'newparticipant@test.com',
            'phone' => '+1234567890',
            'country' => 'USA',
            'nationality' => 'American',
            'birth_date' => '2000-01-01',
        ];

        $response = $this->actingAs($this->agent)
            ->post(route('agent.participants.store'), $participantData);

        $response->assertRedirect(route('agent.participants.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newparticipant@test.com',
            'role' => 'user',
            'created_by_agent_id' => $this->agent->id,
        ]);
    }

    /** @test */
    public function agent_can_assign_program_to_their_participant()
    {
        $response = $this->actingAs($this->agent)
            ->post(route('agent.participants.assign-program.store', $this->participant->id), [
                'program_id' => $this->program->id,
                'notes' => 'Test assignment',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('program_assignments', [
            'user_id' => $this->participant->id,
            'program_id' => $this->program->id,
            'assigned_by' => $this->agent->id,
        ]);
    }

    /** @test */
    public function program_slots_decrement_when_assigned()
    {
        $initialSlots = $this->program->available_slots;

        $this->actingAs($this->agent)
            ->post(route('agent.participants.assign-program.store', $this->participant->id), [
                'program_id' => $this->program->id,
            ]);

        $this->program->refresh();
        $this->assertEquals($initialSlots - 1, $this->program->available_slots);
    }

    /** @test */
    public function agent_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->agent)
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_create_agent()
    {
        $agentData = [
            'name' => 'New Agent',
            'email' => 'newagent@test.com',
            'phone' => '+9876543210',
            'country' => 'Spain',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.agents.store'), $agentData);

        $response->assertRedirect(route('admin.agents.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newagent@test.com',
            'role' => 'agent',
        ]);
    }

    /** @test */
    public function admin_can_view_agent_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.agents.index'));

        $response->assertStatus(200);
        $response->assertSee($this->agent->name);
    }

    /** @test */
    public function admin_can_delete_agent_without_participants()
    {
        $emptyAgent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.agents.destroy', $emptyAgent->id));

        $response->assertRedirect(route('admin.agents.index'));
        $this->assertDatabaseMissing('users', ['id' => $emptyAgent->id]);
    }

    /** @test */
    public function admin_cannot_delete_agent_with_participants()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.agents.destroy', $this->agent->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->agent->id]);
    }

    /** @test */
    public function login_redirects_agent_to_agent_dashboard()
    {
        $agent = User::factory()->create([
            'role' => 'agent',
            'email' => 'test@agent.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@agent.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/agent');
    }

    /** @test */
    public function login_redirects_admin_to_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'test@admin.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@admin.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
    }

    /** @test */
    public function created_by_agent_relationship_works()
    {
        $this->assertNotNull($this->participant->createdByAgent);
        $this->assertEquals($this->agent->id, $this->participant->createdByAgent->id);
    }

    /** @test */
    public function agent_created_participants_relationship_works()
    {
        $this->assertTrue($this->agent->createdParticipants->contains($this->participant));
        $this->assertEquals(1, $this->agent->createdParticipants->count());
    }

    /** @test */
    public function user_policy_allows_agent_to_view_their_participant()
    {
        $this->assertTrue($this->agent->can('view', $this->participant));
    }

    /** @test */
    public function user_policy_denies_agent_viewing_other_agents_participant()
    {
        $otherParticipant = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $this->otherAgent->id,
        ]);

        $this->assertFalse($this->agent->can('view', $otherParticipant));
    }

    /** @test */
    public function agent_can_view_participant_details()
    {
        $response = $this->actingAs($this->agent)
            ->get(route('agent.participants.show', $this->participant->id));

        $response->assertStatus(200);
        $response->assertSee($this->participant->name);
        $response->assertSee($this->participant->email);
    }

    /** @test */
    public function agent_cannot_view_other_agents_participant_details()
    {
        $otherParticipant = User::factory()->create([
            'role' => 'user',
            'created_by_agent_id' => $this->otherAgent->id,
        ]);

        $response = $this->actingAs($this->agent)
            ->get(route('agent.participants.show', $otherParticipant->id));

        $response->assertStatus(404);
    }

    /** @test */
    public function agent_middleware_blocks_non_agents()
    {
        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser)
            ->get(route('agent.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function email_must_be_unique_when_creating_participant()
    {
        $participantData = [
            'name' => 'Duplicate Email',
            'email' => $this->participant->email, // Email ya existente
            'phone' => '+1234567890',
            'country' => 'USA',
            'nationality' => 'American',
            'birth_date' => '2000-01-01',
        ];

        $response = $this->actingAs($this->agent)
            ->post(route('agent.participants.store'), $participantData);

        $response->assertSessionHasErrors('email');
    }
}
