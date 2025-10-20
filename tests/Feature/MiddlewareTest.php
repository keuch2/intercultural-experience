<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_middleware()
    {
        $response = $this->getJson('/api/user');

        // Check that security headers are present
        $response->assertHeader('X-Content-Type-Options', 'nosniff')
                 ->assertHeader('X-Frame-Options', 'DENY')
                 ->assertHeader('X-XSS-Protection', '1; mode=block')
                 ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Check CSP header is present
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        
        // For HTTPS requests, check HSTS
        if ($this->app['request']->isSecure()) {
            $response->assertHeader('Strict-Transport-Security');
        }
    }

    public function test_validate_json_input_middleware_sanitizes_input()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Test that normal input passes through
        $cleanData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a clean message.'
        ];

        $response = $this->postJson('/api/form-submissions', $cleanData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        // Should not be blocked (status might vary based on endpoint existence)
        $this->assertNotEquals(400, $response->getStatusCode());
    }

    public function test_validate_json_input_middleware_blocks_xss()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $xssPayloads = [
            '<script>alert("xss")</script>',
            'javascript:alert("xss")',
            '<img src=x onerror=alert("xss")>',
            '<iframe src="javascript:alert(`xss`)"></iframe>',
            'onclick="alert(\'xss\')"',
            'onload="alert(\'xss\')"',
            'onfocus="alert(\'xss\')"'
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->postJson('/api/form-submissions', [
                'content' => $payload
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);

            $response->assertStatus(400)
                     ->assertJson([
                         'error' => 'Invalid input detected'
                     ]);
        }
    }

    public function test_validate_json_input_middleware_blocks_sql_injection()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $sqlPayloads = [
            "'; DROP TABLE users; --",
            "1' OR '1'='1",
            "UNION SELECT * FROM users",
            "'; INSERT INTO",
            "'; UPDATE users SET",
            "'; DELETE FROM"
        ];

        foreach ($sqlPayloads as $payload) {
            $response = $this->postJson('/api/form-submissions', [
                'query' => $payload
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);

            $response->assertStatus(400)
                     ->assertJson([
                         'error' => 'Invalid input detected'
                     ]);
        }
    }

    public function test_admin_middleware_redirects_non_admin()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(302)
                 ->assertRedirect('/login');
    }

    public function test_admin_middleware_allows_admin_access()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_admin_middleware_redirects_unauthenticated()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_check_role_middleware_api_response()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Try to access an admin-only API endpoint
        $response = $this->getJson('/api/admin/users', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(403)
                 ->assertJsonStructure([
                     'error',
                     'message',
                     'user_role',
                     'required_roles'
                 ]);
    }

    public function test_check_role_middleware_allows_correct_role()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Admin should be able to access admin endpoints
        $response = $this->getJson('/api/admin/users', [
            'Authorization' => 'Bearer ' . $token
        ]);

        // Should not be forbidden (status depends on endpoint existence)
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_activity_logger_middleware_logs_admin_actions()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Perform an admin action
        $response = $this->actingAs($admin)->get('/admin/programs');

        // Check that activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'admin_access',
            'resource_type' => 'programs',
            'ip_address' => '127.0.0.1'
        ]);
    }

    public function test_activity_logger_middleware_logs_failed_access()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Try to access admin area (should fail and be logged)
        $response = $this->actingAs($user)->get('/admin/users');

        // Check that failed access was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'admin_access_denied',
            'resource_type' => 'users'
        ]);
    }

    public function test_activity_logger_sanitizes_sensitive_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Make request with sensitive data in URL or body
        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
            'role' => 'user'
        ]);

        // Check that logged data doesn't contain password
        $log = \App\Models\ActivityLog::where('user_id', $admin->id)
                                     ->where('action', 'admin_access')
                                     ->first();
        
        if ($log) {
            $this->assertStringNotContainsString('StrongP@ss123', $log->request_data);
        }
    }

    public function test_cors_middleware_sets_correct_headers()
    {
        $response = $this->json('OPTIONS', '/api/user');

        // Check CORS headers
        $response->assertHeader('Access-Control-Allow-Origin')
                 ->assertHeader('Access-Control-Allow-Methods')
                 ->assertHeader('Access-Control-Allow-Headers');
    }

    public function test_rate_limiting_middleware_applies_limits()
    {
        // Test that rate limiting is applied at middleware level
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'password'
            ]);
        }

        // 6th request should be rate limited
        $response->assertStatus(429)
                 ->assertHeader('X-RateLimit-Limit')
                 ->assertHeader('X-RateLimit-Remaining');
    }

    public function test_authenticate_middleware_blocks_invalid_tokens()
    {
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer invalid-token'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.'
                 ]);
    }

    public function test_authenticate_middleware_allows_valid_tokens()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200);
    }

    public function test_middleware_stack_order()
    {
        // Test that middleware is applied in correct order
        // SecurityHeaders should be applied even for rate limited requests
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'password'
            ]);
        }

        // Rate limited response should still have security headers
        $response->assertStatus(429)
                 ->assertHeader('X-Content-Type-Options', 'nosniff')
                 ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_json_middleware_handles_malformed_json()
    {
        $response = $this->call('POST', '/api/login', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer test-token'
        ], '{"invalid": json}');

        $response->assertStatus(400);
    }

    public function test_middleware_performance_impact()
    {
        $startTime = microtime(true);

        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Ensure middleware doesn't add significant overhead (< 1 second)
        $this->assertLessThan(1.0, $executionTime);
        $response->assertStatus(200);
    }
}
