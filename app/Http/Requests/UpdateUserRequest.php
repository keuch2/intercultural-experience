<?php

namespace App\Http\Requests;

use App\Rules\StrongPassword;

class UpdateUserRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $commonRules = $this->getCommonRules();
        $userId = $this->route('user')->id ?? $this->route('participant')->id;
        
        return [
            'name' => $commonRules['name'],
            'email' => $commonRules['email'] . '|unique:users,email,' . $userId,
            'password' => ['nullable', 'string', 'confirmed', new StrongPassword()],
            'phone' => $commonRules['phone'],
            'nationality' => $commonRules['nationality'],
            'birth_date' => $commonRules['birth_date'],
            'address' => $commonRules['address'],
            'role' => 'sometimes|required|in:user,admin,agent,finance',
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
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'role.in' => 'El rol seleccionado no es válido.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'nationality.regex' => 'La nacionalidad solo puede contener letras y espacios.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ];
    }
}
