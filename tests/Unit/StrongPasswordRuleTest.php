<?php

namespace Tests\Unit;

use App\Rules\StrongPassword;
use Tests\TestCase;

class StrongPasswordRuleTest extends TestCase
{
    public function test_strong_password_rule_passes_valid_passwords()
    {
        $rule = new StrongPassword();

        $validPasswords = [
            'StrongP@ss123',
            'MySecure!Pass1',
            'Complex$Password9',
            'Tr0ub4dor&3',
            'ValidP@ss2024'
        ];

        foreach ($validPasswords as $password) {
            $failed = false;
            $failMessage = '';
            
            $rule->validate('password', $password, function ($message) use (&$failed, &$failMessage) {
                $failed = true;
                $failMessage = $message;
            });
            
            $this->assertFalse($failed, "Password '{$password}' should be valid but failed with: {$failMessage}");
        }
    }

    public function test_strong_password_rule_fails_weak_passwords()
    {
        $rule = new StrongPassword();

        $weakPasswords = [
            'password',     // Common password
            '12345678',     // Only numbers
            'abcdefgh',     // Only lowercase
            'ABCDEFGH',     // Only uppercase
            'Password',     // Missing special char and number
            'Pass123',      // Too short
            'pass@123',     // Missing uppercase
            'PASS@123',     // Missing lowercase
            'Password123',  // Missing special char
            'Password@',    // Missing number
            '123456789',    // Common weak password
            'qwerty123',    // Common pattern
            'admin123',     // Common admin password
        ];

        foreach ($weakPasswords as $password) {
            $failed = false;
            $rule->validate('password', $password, function ($message) use (&$failed) {
                $failed = true;
            });
            
            $this->assertTrue($failed, "Password '{$password}' should be invalid");
        }
    }


    public function test_strong_password_rule_provides_detailed_feedback()
    {
        $rule = new StrongPassword();
        
        // Test that rule provides specific feedback for different violations
        $messages = [];
        
        $rule->validate('password', 'weak', function ($message) use (&$messages) {
            $messages[] = $message;
        });
        
        // Should have at least one validation message
        $this->assertNotEmpty($messages);
        $this->assertIsString($messages[0]);
    }
}
