<?php

namespace Tests\Feature;

use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_program_request_validation_rules()
    {
        $request = new StoreProgramRequest();
        $rules = $request->rules();

        // Test that all required fields are present in rules
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('country', $rules);
        $this->assertArrayHasKey('category', $rules);
        $this->assertArrayHasKey('capacity', $rules);
        $this->assertArrayHasKey('cost', $rules);

        // Test validation passes with valid data
        $validData = [
            'name' => 'Test Program',
            'description' => 'This is a test program description that is long enough.',
            'country' => 'Spain',
            'category' => 'Work and Travel',
            'location' => 'Madrid',
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
            'application_deadline' => '2024-05-01',
            'duration' => '6 months',
            'credits' => 30,
            'capacity' => 20,
            'cost' => 5000.00,
            'currency_id' => 1,
            'is_active' => true,
            'main_category' => 'IE',
            'subcategory' => 'Work and Travel'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test validation fails with invalid data
        $invalidData = [
            'name' => '', // Empty name
            'description' => '', // Empty description
            'country' => str_repeat('a', 101), // Too long
            'start_date' => 'invalid-date',
            'end_date' => '2024-01-01', // Before start date
            'cost' => -100, // Negative cost
            'capacity' => 0, // Zero capacity
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
    }

    public function test_update_program_request_validation_rules()
    {
        $request = new UpdateProgramRequest();
        $rules = $request->rules();

        // Test that validation handles optional fields correctly
        $partialUpdateData = [
            'name' => 'Updated Program Name',
            'cost' => 6000.00
        ];

        $validator = Validator::make($partialUpdateData, $rules);
        $this->assertFalse($validator->fails());

        // Test date relationship validation
        $invalidDateData = [
            'start_date' => '2024-12-31',
            'end_date' => '2024-06-01', // End before start
            'application_deadline' => '2025-01-01' // After start date
        ];

        $validator = Validator::make($invalidDateData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('end_date'));
        $this->assertTrue($validator->errors()->has('application_deadline'));
    }

    public function test_store_user_request_validation_rules()
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();

        // Test strong password validation
        $validUserData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
            'phone' => '+1234567890',
            'nationality' => 'American',
            'birth_date' => '1990-01-01',
            'address' => '123 Main St',
            'role' => 'user'
        ];

        $validator = Validator::make($validUserData, $rules);
        $this->assertFalse($validator->fails());

        // Test unique email validation
        User::factory()->create(['email' => 'existing@example.com']);

        $duplicateEmailData = $validUserData;
        $duplicateEmailData['email'] = 'existing@example.com';

        $validator = Validator::make($duplicateEmailData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('email'));
    }

    public function test_update_user_request_validation_rules()
    {
        $user = User::factory()->create(['email' => 'original@example.com']);
        
        $request = new UpdateUserRequest();
        // Mock the route parameter
        $request->setRouteResolver(function () use ($user) {
            return new class($user) {
                public function __construct(private $user) {}
                public function parameter($key) {
                    return $this->user;
                }
            };
        });
        
        $rules = $request->rules();

        // Test that email uniqueness excludes current user
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'original@example.com', // Same email should be allowed
            'role' => 'admin'
        ];

        $validator = Validator::make($updateData, $rules);
        $this->assertFalse($validator->fails());

        // Test password is optional on update
        $noPasswordData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $validator = Validator::make($noPasswordData, $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_store_application_request_validation_rules()
    {
        $program = \App\Models\Program::factory()->create();
        $request = new StoreApplicationRequest();
        $rules = $request->rules();

        // Test valid application data
        $validApplicationData = [
            'program_id' => $program->id,
            'motivation_letter' => 'I am very motivated to participate in this program because...',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '+1234567890',
            'emergency_contact_relationship' => 'Mother',
            'medical_conditions' => 'No known medical conditions',
            'special_requirements' => 'Vegetarian meals preferred'
        ];

        $validator = Validator::make($validApplicationData, $rules);
        $this->assertFalse($validator->fails());

        // Test invalid program_id
        $invalidProgramData = $validApplicationData;
        $invalidProgramData['program_id'] = 999999;

        $validator = Validator::make($invalidProgramData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('program_id'));

        // Test field length limits
        $longTextData = $validApplicationData;
        $longTextData['motivation_letter'] = str_repeat('a', 2001); // Exceeds 2000 char limit

        $validator = Validator::make($longTextData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('motivation_letter'));
    }

    public function test_form_request_custom_messages()
    {
        $request = new StoreProgramRequest();
        $messages = $request->messages();

        // Test that custom messages are defined
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('description.required', $messages);
        
        // Test messages are in Spanish
        $this->assertStringContainsString('obligatorio', $messages['name.required']);
    }

    public function test_base_form_request_common_rules()
    {
        $request = new StoreProgramRequest();
        $commonRules = $request->getCommonRules();

        // Test that common rules are available
        $this->assertIsArray($commonRules);
        $this->assertArrayHasKey('name', $commonRules);
        $this->assertArrayHasKey('email', $commonRules);
        
        // Test name validation includes regex for letters and spaces
        $this->assertStringContainsString('regex', $commonRules['name']);
    }

    public function test_form_request_authorization()
    {
        // Test that form requests return true for authorization
        // (since we handle authorization via middleware)
        
        $storeProgramRequest = new StoreProgramRequest();
        $this->assertTrue($storeProgramRequest->authorize());

        $storeUserRequest = new StoreUserRequest();
        $this->assertTrue($storeUserRequest->authorize());
        
        $storeApplicationRequest = new StoreApplicationRequest();
        $this->assertTrue($storeApplicationRequest->authorize());
    }

    public function test_form_request_validation_in_controller_context()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test that Form Request validation is applied in controller
        $response = $this->actingAs($admin)->postJson('/admin/programs', []);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'description', 'country']);

        // Test with valid data
        $validData = [
            'name' => 'Test Program',
            'description' => 'A comprehensive test program for students.',
            'country' => 'Spain',
            'category' => 'Study Abroad',
            'capacity' => 20,
            'cost' => 5000,
            'currency_id' => 1,
            'is_active' => true,
            'main_category' => 'IE',
            'subcategory' => 'Study Abroad'
        ];

        $response = $this->actingAs($admin)->postJson('/admin/programs', $validData);
        
        // Should not have validation errors (status may vary based on other factors)
        if ($response->getStatusCode() === 422) {
            $errors = $response->json('errors');
            $this->assertEmpty(array_intersect(['name', 'description', 'country'], array_keys($errors)));
        }
    }

    public function test_phone_regex_validation()
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();
        
        $validPhones = [
            '+1234567890',
            '(123) 456-7890',
            '123-456-7890',
            '+1 (123) 456-7890'
        ];

        foreach ($validPhones as $phone) {
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'StrongP@ss123',
                'password_confirmation' => 'StrongP@ss123',
                'role' => 'user',
                'phone' => $phone
            ];

            $validator = Validator::make($data, $rules);
            $phoneErrors = $validator->errors()->get('phone');
            $this->assertEmpty($phoneErrors, "Phone {$phone} should be valid");
        }
    }

    public function test_birth_date_validation()
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();
        
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongP@ss123',
            'password_confirmation' => 'StrongP@ss123',
            'role' => 'user',
            'birth_date' => now()->addDays(1)->format('Y-m-d') // Future date
        ];

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('birth_date'));
    }
}
