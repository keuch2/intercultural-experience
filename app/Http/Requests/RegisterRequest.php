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
            'email' => $commonRules['email'].'|unique:users',
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
            'phone' => $commonRules['phone'],
            'nationality' => $commonRules['nationality'],
            'birth_date' => $commonRules['birth_date'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'academic_level' => ['nullable', 'in:bachiller,licenciatura,maestria,posgrado,doctorado'],
            'english_level' => ['nullable', 'in:basico,intermedio,avanzado,nativo'],
            // Opcional para no romper versiones viejas de la app; las nuevas lo envían siempre.
            'accept_terms' => ['sometimes', 'accepted'],
        ];
    }
}
