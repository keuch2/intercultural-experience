<?php

namespace App\Http\Requests;

use App\Rules\StrongPassword;
use Illuminate\Validation\Rule;

class RegisterRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $commonRules = $this->getCommonRules();

        $rules = [
            'name' => $commonRules['name'],
            'email' => $commonRules['email'].'|unique:users',
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
            'phone' => $commonRules['phone'],
            'nationality' => $commonRules['nationality'],
            'birth_date' => $commonRules['birth_date'],
            'city' => ['nullable', 'string', 'max:100'],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'country' => ['nullable', 'string', 'max:100'],
            'academic_level' => ['nullable', 'in:bachiller,licenciatura,maestria,posgrado,doctorado'],
            'english_level' => ['nullable', 'in:basico,intermedio,avanzado,nativo'],
            // Opcional para no romper versiones viejas de la app; las nuevas lo envían siempre.
            'accept_terms' => ['sometimes', 'accepted'],
        ];

        // Formulario nuevo (pedido de IE, nov 2026): todo obligatorio, con fecha de nacimiento y teléfono.
        // Las versiones viejas de la app no envían registration_version y siguen con las reglas de arriba.
        if ($this->isStrict()) {
            $rules['phone'] = ['required', 'string', 'max:50', 'regex:/^[\+]?[\d\s\-\(\)]{7,}$/'];
            $rules['birth_date'] = ['required', 'date', 'before:today', 'after:1900-01-01'];
            $rules['country'] = ['required', 'string', 'max:100'];
            $rules['city'] = ['required_without:city_id', 'nullable', 'string', 'min:2', 'max:100'];
            $rules['academic_level'][0] = 'required';
            $rules['english_level'][0] = 'required';
            $rules['accept_terms'] = ['required', 'accepted'];
        }

        return $rules;
    }

    public function isStrict(): bool
    {
        return (int) $this->input('registration_version') >= 2;
    }

    public function attributes(): array
    {
        return [
            'phone' => 'teléfono', 'birth_date' => 'fecha de nacimiento', 'country' => 'país', 'city' => 'ciudad',
            'city_id' => 'ciudad', 'academic_level' => 'nivel académico', 'english_level' => 'nivel de inglés',
            'accept_terms' => 'términos y condiciones',
        ];
    }
}
