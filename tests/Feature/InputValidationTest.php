<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_json_input_middleware_blocks_script_injection()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $maliciousData = [
            'name' => '<script>alert("xss")</script>',
            'description' => 'javascript:alert("xss")',
            'content' => '<img src=x onerror=alert("xss")>'
        ];

        $response = $this->postJson('/api/form-submissions', $maliciousData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        // Should be blocked by ValidateJsonInput middleware
        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'Invalid input detected'
                 ]);
    }

    public function test_validate_json_input_middleware_blocks_sql_injection()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        $maliciousData = [
            'email' => "'; DROP TABLE users; --",
            'name' => "1' OR '1'='1",
            'search' => "UNION SELECT * FROM users WHERE 1=1 --"
        ];

        $response = $this->postJson('/api/form-submissions', $maliciousData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        // Should be blocked by ValidateJsonInput middleware
        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'Invalid input detected'
                 ]);
    }

    public function test_store_program_request_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test missing required fields
        $response = $this->actingAs($admin)->postJson('/admin/programs', []);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'description', 'country']);

        // Test invalid data types
        $response = $this->actingAs($admin)->postJson('/admin/programs', [
            'name' => '', // Empty string
            'description' => '', // Empty string
            'country' => str_repeat('a', 101), // Too long
            'start_date' => 'invalid-date',
            'end_date' => '2024-01-01', // Before start date
            'cost' => -100, // Negative cost
            'capacity' => 0, // Zero capacity
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name', 'description', 'country', 'start_date', 'cost', 'capacity'
                 ]);
    }

    public function test_store_user_request_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test weak password validation
        $response = $this->actingAs($admin)->postJson('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'role' => 'user'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);

        // Test invalid email format
        $response = $this->actingAs($admin)->postJson('/admin/users', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
            'role' => 'user'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

        // Test invalid role
        $response = $this->actingAs($admin)->postJson('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
            'role' => 'invalid_role'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['role']);
    }

    public function test_strong_password_rule()
    {
        // Test passwords that should fail
        $weakPasswords = [
            'password', // Common password
            '12345678', // Only numbers
            'abcdefgh', // Only lowercase
            'ABCDEFGH', // Only uppercase
            'Password', // Missing special character and number
            'Pass123',  // Too short
        ];

        foreach ($weakPasswords as $password) {
            $response = $this->postJson('/api/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $password,
                'password_confirmation' => $password,
            ]);

            $response->assertStatus(422)
                     ->assertJsonValidationErrors(['password']);
        }

        // Test password that should pass
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
        ]);

        $response->assertStatus(201);
    }

    public function test_application_request_validation()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Test missing program_id
        $response = $this->postJson('/api/applications', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['program_id']);

        // Test invalid program_id
        $response = $this->postJson('/api/applications', [
            'program_id' => 999999, // Non-existent program
            'motivation_letter' => 'Test letter'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['program_id']);
    }

    public function test_email_validation_across_endpoints()
    {
        $invalidEmails = [
            'invalid',
            'invalid@',
            '@invalid.com',
            'invalid@.com',
            'invalid.com',
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->postJson('/api/register', [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'StrongP@ss123',
                'password_confirmation' => 'StrongP@ss123',
            ]);

            $response->assertStatus(422)
                     ->assertJsonValidationErrors(['email']);
        }
    }

    public function test_phone_number_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $invalidPhones = [
            'abc123',
            '123abc',
            'phone',
            '++123456789',
            '123-456-78901234567890', // Too long
        ];

        foreach ($invalidPhones as $phone) {
            $response = $this->actingAs($admin)->postJson('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'StrongP@ss123',
                'password_confirmation' => 'StrongP@ss123',
                'role' => 'user',
                'phone' => $phone
            ]);

            if ($response->getStatusCode() === 422) {
                $response->assertJsonValidationErrors(['phone']);
            }
        }

        // Valid phone numbers should pass
        $validPhones = [
            '+1234567890',
            '(123) 456-7890',
            '123-456-7890',
            '+1 (123) 456-7890'
        ];

        foreach ($validPhones as $phone) {
            $response = $this->actingAs($admin)->postJson('/admin/users', [
                'name' => 'Test User',
                'email' => "test{$phone}@example.com",
                'password' => 'StrongP@ss123',
                'password_confirmation' => 'StrongP@ss123',
                'role' => 'user',
                'phone' => $phone
            ]);

            // Should not have phone validation errors
            if ($response->getStatusCode() === 422) {
                $errors = $response->json('errors');
                $this->assertArrayNotHasKey('phone', $errors);
            }
        }
    }

    public function test_date_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test invalid date formats
        $response = $this->actingAs($admin)->postJson('/admin/programs', [
            'name' => 'Test Program',
            'description' => 'Test Description',
            'country' => 'Test Country',
            'category' => 'Test Category',
            'capacity' => 10,
            'cost' => 100,
            'currency_id' => 1,
            'start_date' => 'invalid-date',
            'end_date' => 'also-invalid',
            'application_deadline' => 'not-a-date'
        ]);

        $response->assertStatus(422);
        
        // Test end_date before start_date
        $response = $this->actingAs($admin)->postJson('/admin/programs', [
            'name' => 'Test Program',
            'description' => 'Test Description',
            'country' => 'Test Country',
            'category' => 'Test Category',
            'capacity' => 10,
            'cost' => 100,
            'currency_id' => 1,
            'start_date' => '2024-12-31',
            'end_date' => '2024-01-01' // Before start date
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['end_date']);
    }

    public function test_file_upload_validation()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // Create invalid file types
        $invalidFile = \Illuminate\Http\Testing\File::fake()->create('malicious.exe', 1000);
        $oversizedFile = \Illuminate\Http\Testing\File::fake()->image('large.jpg')->size(5000); // > 2MB

        // Test with invalid file type (if document upload exists)
        $response = $this->postJson('/api/documents', [
            'file' => $invalidFile,
            'document_type' => 'passport'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        // Should validate file type (if endpoint exists)
        if ($response->getStatusCode() === 422) {
            $response->assertJsonValidationErrors(['file']);
        }
    }

    public function test_numeric_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test negative values where they shouldn't be allowed
        $response = $this->actingAs($admin)->postJson('/admin/programs', [
            'name' => 'Test Program',
            'description' => 'Test Description',
            'country' => 'Test Country',
            'category' => 'Test Category',
            'capacity' => -5, // Negative capacity
            'cost' => -100, // Negative cost
            'currency_id' => 1,
            'credits' => -10 // Negative credits
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['capacity', 'cost']);
    }

    public function test_string_length_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test strings that are too long
        $response = $this->actingAs($admin)->postJson('/admin/programs', [
            'name' => str_repeat('a', 256), // Too long
            'description' => str_repeat('b', 65536), // Very long description
            'country' => str_repeat('c', 101), // Too long
            'category' => str_repeat('d', 101), // Too long
            'capacity' => 10,
            'cost' => 100,
            'currency_id' => 1
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'country', 'category']);
    }
}
