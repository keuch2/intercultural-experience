<?php

namespace App\Http\Requests;

use App\Rules\StrongPassword;

class RegisterRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $commonRules = $this->getCommonRules();
        
        return [
            'name' => $commonRules['name'],
            'email' => $commonRules['email'] . '|unique:users',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
            'phone' => $commonRules['phone'],
            'nationality' => $commonRules['nationality'],
            'birth_date' => $commonRules['birth_date'],
        ];
    }
}
