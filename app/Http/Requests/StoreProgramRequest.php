<?php

namespace App\Http\Requests;

class StoreProgramRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'country' => 'required|string|max:100',
            'subcategory' => 'required|string|max:100',
            'duration_weeks' => 'required|integer|min:1|max:104',
            'min_age' => 'required|integer|min:14|max:65',
            'max_age' => 'required|integer|min:14|max:65|gte:min_age',
            'cost' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'max_participants' => 'nullable|integer|min:1|max:1000',
            'application_deadline' => 'nullable|date|after:today',
            'program_start_date' => 'nullable|date|after:application_deadline',
            'program_end_date' => 'nullable|date|after:program_start_date',
            'requirements' => 'nullable|string|max:2000',
            'benefits' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:255',
            'institution_id' => 'nullable|exists:institutions,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brochure' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del programa es obligatorio.',
            'description.required' => 'La descripción del programa es obligatoria.',
            'country.required' => 'El país del programa es obligatorio.',
            'subcategory.required' => 'La subcategoría del programa es obligatoria.',
            'duration_weeks.required' => 'La duración en semanas es obligatoria.',
            'min_age.required' => 'La edad mínima es obligatoria.',
            'max_age.required' => 'La edad máxima es obligatoria.',
            'max_age.gte' => 'La edad máxima debe ser mayor o igual a la edad mínima.',
            'cost.required' => 'El costo del programa es obligatorio.',
            'currency_id.required' => 'La moneda es obligatoria.',
            'currency_id.exists' => 'La moneda seleccionada no es válida.',
            'application_deadline.after' => 'La fecha límite de aplicación debe ser posterior a hoy.',
            'program_start_date.after' => 'La fecha de inicio debe ser posterior a la fecha límite.',
            'program_end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.max' => 'La imagen no debe ser mayor a 2MB.',
            'brochure.mimes' => 'El folleto debe ser un archivo PDF.',
            'brochure.max' => 'El folleto no debe ser mayor a 10MB.',
        ];
    }
}
