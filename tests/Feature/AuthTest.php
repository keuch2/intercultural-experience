<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_strong_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'user' => ['id', 'name', 'email'],
                     'token'
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }

    public function test_user_cannot_register_with_weak_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_register_with_common_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongP@ss123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'user',
                     'token'
                 ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongP@ss123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error'
                 ]);
    }

    public function test_admin_cannot_login_through_mobile_api()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('StrongP@ss123'),
            'role' => 'admin'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'StrongP@ss123'
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'status' => 'error'
                 ]);
    }

    public function test_rate_limiting_on_login_endpoint()
    {
        // Attempt 6 login requests rapidly (limit is 5 per minute)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'password123'
            ]);
        }

        // The 6th request should be rate limited
        $response->assertStatus(429);
    }

    public function test_user_can_logout_with_valid_token()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Cierre de sesión exitoso'
                 ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_user_cannot_logout_without_token()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_user_gets_profile_with_valid_token()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);

        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'user' => ['id', 'name', 'email', 'role']
                 ])
                 ->assertJson([
                     'user' => [
                         'name' => 'Test User',
                         'email' => 'test@example.com',
                         'role' => 'user'
                     ]
                 ]);
    }

    public function test_user_registration_forces_user_role()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
            'role' => 'admin' // This should be ignored
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals('user', $user->role);
    }

    public function test_token_expiration_is_set_correctly()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongP@ss123'),
            'role' => 'user'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123'
        ]);

        $response->assertStatus(200);
        
        // Verify token has expiration date (30 days from now)
        $token = $user->tokens()->first();
        $this->assertNotNull($token->expires_at);
        $this->assertTrue($token->expires_at->isAfter(now()->addDays(29)));
        $this->assertTrue($token->expires_at->isBefore(now()->addDays(31)));
    }

    public function test_previous_tokens_are_deleted_on_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('StrongP@ss123'),
            'role' => 'user'
        ]);

        // Create an existing token
        $oldToken = $user->createToken('old-token')->plainTextToken;
        
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123'
        ]);

        $response->assertStatus(200);
        
        // Verify old token was deleted and only one token exists
        $this->assertEquals(1, $user->tokens()->count());
    }
}
