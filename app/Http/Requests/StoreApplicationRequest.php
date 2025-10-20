<?php

namespace App\Http\Requests;

class StoreApplicationRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'program_id' => 'required|exists:programs,id',
            'status' => 'sometimes|in:pending,approved,rejected',
            'application_notes' => 'nullable|string|max:1000',
            'motivation_letter' => 'nullable|string|max:2000',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50|regex:/^[\+]?[\d\s\-\(\)]+$/',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'medical_conditions' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'El programa es obligatorio.',
            'program_id.exists' => 'El programa seleccionado no es válido.',
            'status.in' => 'El estado seleccionado no es válido.',
            'motivation_letter.max' => 'La carta de motivación no puede exceder 2000 caracteres.',
            'emergency_contact_phone.regex' => 'El formato del teléfono de emergencia no es válido.',
            'medical_conditions.max' => 'Las condiciones médicas no pueden exceder 1000 caracteres.',
            'special_requirements.max' => 'Los requerimientos especiales no pueden exceder 1000 caracteres.',
        ];
    }
}
