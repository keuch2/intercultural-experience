<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 8) {
            $fail('La contraseña debe tener al menos 8 caracteres.');
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('La contraseña debe contener al menos una letra minúscula.');
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $fail('La contraseña debe contener al menos una letra mayúscula.');
        }

        if (!preg_match('/[0-9]/', $value)) {
            $fail('La contraseña debe contener al menos un número.');
        }

        // Check for common weak passwords
        $commonPasswords = [
            'password', '12345678', 'qwerty123', 'admin123', 
            'password123', '123456789', 'welcome123'
        ];

        if (in_array(strtolower($value), $commonPasswords)) {
            $fail('Esta contraseña es muy común. Por favor, elige una contraseña más segura.');
        }
    }
}
