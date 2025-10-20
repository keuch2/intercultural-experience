<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rate_limiting()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongP@ss123'),
            'role' => 'user'
        ]);

        // Make 5 successful requests (within limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'StrongP@ss123'
            ]);
            $response->assertStatus(200);
        }

        // 6th request should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123'
        ]);
        $response->assertStatus(429);
    }

    public function test_register_rate_limiting()
    {
        // Make 5 registration requests (within limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/register', [
                'name' => "Test User $i",
                'email' => "test$i@example.com",
                'password' => 'StrongP@ss123',
                'password_confirmation' => 'StrongP@ss123',
            ]);
            $response->assertStatus(201);
        }

        // 6th request should be rate limited
        $response = $this->postJson('/api/register', [
            'name' => 'Test User 6',
            'email' => 'test6@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
        ]);
        $response->assertStatus(429);
    }

    public function test_applications_rate_limiting()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;
        $programs = Program::factory()->count(15)->create();

        // Make 10 application requests (within limit)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/applications', [
                'program_id' => $programs[$i]->id,
                'motivation_letter' => 'Test motivation letter'
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            $response->assertStatus(201);
        }

        // 11th request should be rate limited
        $response = $this->postJson('/api/applications', [
            'program_id' => $programs[10]->id,
            'motivation_letter' => 'Test motivation letter'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(429);
    }

    public function test_application_documents_rate_limiting()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;
        $program = Program::factory()->create();
        $application = Application::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id
        ]);

        // Create a fake file for testing
        $file = \Illuminate\Http\Testing\File::fake()->create('document.pdf', 100);

        // Make 5 document upload requests (within limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson("/api/applications/{$application->id}/documents", [
                'document_type' => 'passport',
                'file' => $file
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            // Don't assert status as endpoint might not exist, just check rate limiting
        }

        // 6th request should be rate limited
        $response = $this->postJson("/api/applications/{$application->id}/documents", [
            'document_type' => 'passport',
            'file' => $file
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(429);
    }

    public function test_support_tickets_rate_limiting()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Make 3 support ticket requests (within limit)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/support-tickets', [
                'subject' => "Test Subject $i",
                'message' => "Test message $i",
                'priority' => 'medium'
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            // Don't assert specific status as endpoint might not exist
        }

        // 4th request should be rate limited
        $response = $this->postJson('/api/support-tickets', [
            'subject' => 'Test Subject 4',
            'message' => 'Test message 4',
            'priority' => 'medium'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(429);
    }

    public function test_form_submissions_rate_limiting()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Make 10 form submission requests (within limit)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/form-submissions', [
                'form_id' => 1,
                'data' => ['field1' => "value$i"]
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            // Don't assert specific status as endpoint might not exist
        }

        // 11th request should be rate limited
        $response = $this->postJson('/api/form-submissions', [
            'form_id' => 1,
            'data' => ['field1' => 'value11']
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(429);
    }

    public function test_program_requisites_completion_rate_limiting()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;
        $program = Program::factory()->create();

        // Make 10 requisite completion requests (within limit)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson("/api/programs/{$program->id}/requisites/complete", [
                'requisite_id' => $i + 1
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            // Don't assert specific status as endpoint might not exist
        }

        // 11th request should be rate limited
        $response = $this->postJson("/api/programs/{$program->id}/requisites/complete", [
            'requisite_id' => 11
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(429);
    }

    public function test_redemptions_rate_limiting()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Make 5 redemption requests (within limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/redemptions', [
                'reward_id' => 1,
                'points_used' => 100
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            // Don't assert specific status as endpoint might not exist
        }

        // 6th request should be rate limited
        $response = $this->postJson('/api/redemptions', [
            'reward_id' => 1,
            'points_used' => 100
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $response->assertStatus(429);
    }

    public function test_rate_limiting_resets_after_time()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongP@ss123'),
            'role' => 'user'
        ]);

        // Hit rate limit
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'StrongP@ss123'
            ]);
        }

        // Last response should be rate limited
        $this->assertEquals(429, $response->getStatusCode());

        // Travel forward in time (simulate cache expiration)
        $this->travel(65)->seconds();

        // Should be able to make requests again
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123'
        ]);
        
        $response->assertStatus(200);
    }

    public function test_rate_limiting_headers_are_present()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        // Check for rate limiting headers
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
    }
}
