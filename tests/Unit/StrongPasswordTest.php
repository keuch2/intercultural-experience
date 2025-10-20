<?php

namespace Tests\Unit;

use App\Rules\StrongPassword;
use PHPUnit\Framework\TestCase;

class StrongPasswordTest extends TestCase
{
    public function test_strong_password_passes_validation()
    {
        $rule = new StrongPassword();
        $validPasswords = [
            'StrongP@ss123',
            'MySecure@Pass1',
            'Complex$Password2024',
        ];

        foreach ($validPasswords as $password) {
            $failed = false;
            $rule->validate('password', $password, function() use (&$failed) {
                $failed = true;
            });
            
            $this->assertFalse($failed, "Password '{$password}' should be valid");
        }
    }

    public function test_weak_passwords_fail_validation()
    {
        $rule = new StrongPassword();
        $weakPasswords = [
            'short',           // Too short
            'nouppercase1@',   // No uppercase
            'NOLOWERCASE1@',   // No lowercase
            'NoNumbers@',      // No numbers
            'NoSpecialChar1',  // No special characters
            'password123',     // Common password
            'admin123',        // Common password
        ];

        foreach ($weakPasswords as $password) {
            $failed = false;
            $rule->validate('password', $password, function() use (&$failed) {
                $failed = true;
            });
            
            $this->assertTrue($failed, "Password '{$password}' should be invalid");
        }
    }
}
