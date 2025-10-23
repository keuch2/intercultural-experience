<?php

namespace App\Http\Requests;

use App\Rules\StrongPassword;

class StoreUserRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $commonRules = $this->getCommonRules();
        
        return [
            'name' => $commonRules['name'],
            'email' => $commonRules['email'] . '|unique:users,email',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
            'phone' => $commonRules['phone'],
            'nationality' => $commonRules['nationality'],
            'birth_date' => $commonRules['birth_date'],
            'address' => $commonRules['address'],
            'role' => 'required|in:user,admin,agent,finance',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'academic_level' => 'nullable|string|max:100',
            'english_level' => 'nullable|string|max:50',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'nationality.regex' => 'La nacionalidad solo puede contener letras y espacios.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ];
    }
}
