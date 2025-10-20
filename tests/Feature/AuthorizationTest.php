<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Run seeders to have basic data
    }

    public function test_admin_can_access_admin_panel()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_admin_panel()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(302); // Redirected to login with logout
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_manage_programs()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Test admin can view programs
        $response = $this->actingAs($admin)->get('/admin/programs');
        $response->assertStatus(200);

        // Test admin can create programs
        $response = $this->actingAs($admin)->get('/admin/programs/create');
        $response->assertStatus(200);
    }

    public function test_admin_can_manage_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/users/create');
        $response->assertStatus(200);
    }

    public function test_user_can_access_own_profile_via_api()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'user' => [
                         'id' => $user->id,
                         'email' => $user->email
                     ]
                 ]);
    }

    public function test_user_cannot_access_other_users_data()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $token = $user1->createToken('mobile-app-token')->plainTextToken;

        $response = $this->getJson("/api/users/{$user2->id}", [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(404); // Not found or forbidden
    }

    public function test_admin_role_check_middleware()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        // User should be denied access to admin routes
        $response = $this->actingAs($user)->get('/admin/programs');
        $response->assertStatus(302);

        // Admin should have access
        $response = $this->actingAs($admin)->get('/admin/programs');
        $response->assertStatus(200);
    }

    public function test_api_role_restrictions()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        
        $userToken = $user->createToken('mobile-app-token')->plainTextToken;
        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        // User should access user endpoints
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $userToken
        ]);
        $response->assertStatus(200);

        // Admin token should also work for API (if needed for testing)
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $adminToken
        ]);
        $response->assertStatus(200);
    }

    public function test_sensitive_data_is_hidden_from_responses()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'bank_info' => encrypt('1234567890')
        ]);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
                 ->assertJsonMissing(['bank_info']);
    }

    public function test_application_access_restrictions()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $program = Program::factory()->create();
        
        $application = Application::factory()->create([
            'user_id' => $user1->id,
            'program_id' => $program->id
        ]);

        $user2Token = $user2->createToken('mobile-app-token')->plainTextToken;

        // User 2 should not be able to access User 1's application
        $response = $this->getJson("/api/applications/{$application->id}", [
            'Authorization' => 'Bearer ' . $user2Token
        ]);

        $response->assertStatus(403); // Forbidden or not found
    }

    public function test_middleware_handles_invalid_tokens()
    {
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer invalid-token'
        ]);

        $response->assertStatus(401);
    }

    public function test_middleware_handles_expired_tokens()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        // Create an expired token
        $token = $user->createToken('expired-token', ['*'], now()->subDay());
        
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(401);
    }

    public function test_check_role_middleware_with_json_response()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Attempt to access an admin-only API endpoint (if any exist)
        $response = $this->getJson('/api/admin/users', [
            'Authorization' => 'Bearer ' . $token
        ]);

        // Should return JSON error response
        $response->assertStatus(403)
                 ->assertJson([
                     'error' => 'Unauthorized'
                 ]);
    }

    public function test_activity_logger_middleware_logs_admin_actions()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Perform an admin action
        $response = $this->actingAs($admin)->get('/admin/programs');
        
        // Check that activity was logged (assuming we have activity_logs table)
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'admin_access',
            'resource_type' => 'programs'
        ]);
    }
}
