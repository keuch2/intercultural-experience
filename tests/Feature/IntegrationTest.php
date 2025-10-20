<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_user_application_flow()
    {
        // Create a program
        $program = Program::factory()->create([
            'name' => 'Test Exchange Program',
            'capacity' => 10,
            'is_active' => true
        ]);

        // User registers via API
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123'
        ]);

        $registerResponse->assertStatus(201);
        $user = User::where('email', 'john@example.com')->first();
        $this->assertEquals('user', $user->role);

        // User logs in
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'StrongP@ss123'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');

        // User views available programs
        $programsResponse = $this->getJson('/api/programs', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $programsResponse->assertStatus(200);

        // User applies to program
        $applicationResponse = $this->postJson('/api/applications', [
            'program_id' => $program->id,
            'motivation_letter' => 'I am very interested in this program because...'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $applicationResponse->assertStatus(201);

        // Verify application was created
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'pending'
        ]);
    }

    public function test_admin_workflow()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => 'admin']);

        // Admin logs into web panel
        $this->actingAs($admin);

        // Admin views dashboard
        $dashboardResponse = $this->get('/admin');
        $dashboardResponse->assertStatus(200);

        // Admin creates a new program
        $programData = [
            'name' => 'New Exchange Program',
            'description' => 'A comprehensive exchange program for students.',
            'country' => 'Spain',
            'category' => 'Study Abroad',
            'capacity' => 25,
            'cost' => 5000,
            'currency_id' => 1,
            'is_active' => true,
            'main_category' => 'IE',
            'subcategory' => 'Study Abroad'
        ];

        $createProgramResponse = $this->post('/admin/programs', $programData);
        
        // Should redirect after successful creation
        $createProgramResponse->assertStatus(302);

        // Verify program was created
        $this->assertDatabaseHas('programs', [
            'name' => 'New Exchange Program',
            'country' => 'Spain'
        ]);
    }

    public function test_role_separation_enforcement()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        // User cannot access admin panel
        $this->actingAs($user);
        $adminResponse = $this->get('/admin');
        $adminResponse->assertStatus(302); // Redirected

        // Admin cannot login via mobile API
        $mobileLoginResponse = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password'
        ]);
        
        $mobileLoginResponse->assertStatus(403);
    }

    public function test_data_encryption_and_hiding()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'bank_info' => encrypt('1234567890')
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Get user profile via API
        $profileResponse = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $profileResponse->assertStatus(200)
                       ->assertJsonMissing(['bank_info']);

        // Verify bank_info is still encrypted in database
        $freshUser = User::find($user->id);
        $this->assertNotEquals('1234567890', $freshUser->getAttributes()['bank_info']);
        $this->assertEquals('1234567890', $freshUser->bank_info); // Decrypted via accessor
    }

    public function test_rate_limiting_across_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Test login rate limiting
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'wrongpassword'
            ]);
        }
        $response->assertStatus(429);

        // Test authenticated endpoint rate limiting
        $program = Program::factory()->create();
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/applications', [
                'program_id' => $program->id,
                'motivation_letter' => 'Test application'
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
        }
        $response->assertStatus(429);
    }

    public function test_input_validation_and_sanitization()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Test XSS prevention
        $maliciousData = [
            'content' => '<script>alert("xss")</script>',
            'name' => 'javascript:alert("xss")'
        ];

        $response = $this->postJson('/api/form-submissions', $maliciousData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Invalid input detected']);
    }

    public function test_middleware_security_headers()
    {
        $response = $this->getJson('/api/user');

        // Verify security headers are present
        $response->assertHeader('X-Content-Type-Options', 'nosniff')
                 ->assertHeader('X-Frame-Options', 'DENY')
                 ->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_form_request_validation_integration()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test that Form Request validation works in actual controller
        $invalidProgramData = [
            'name' => '', // Invalid
            'description' => '', // Invalid
            'cost' => -100, // Invalid
            'capacity' => 0 // Invalid
        ];

        $response = $this->actingAs($admin)->postJson('/admin/programs', $invalidProgramData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'description', 'cost', 'capacity']);
    }

    public function test_activity_logging()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Perform admin action
        $this->actingAs($admin)->get('/admin/programs');

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'admin_access'
        ]);
    }

    public function test_token_cleanup_and_expiration()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Create multiple tokens
        $token1 = $user->createToken('token1')->plainTextToken;
        $token2 = $user->createToken('token2')->plainTextToken;

        $this->assertEquals(2, $user->tokens()->count());

        // Login should delete previous tokens
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $this->assertEquals(1, $user->fresh()->tokens()->count());

        // Verify new token has expiration
        $newToken = $user->tokens()->first();
        $this->assertNotNull($newToken->expires_at);
        $this->assertTrue($newToken->expires_at->isAfter(now()));
    }
}
