<?php

namespace App\Http\Requests;

class StoreProgramFormRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'version' => 'required|string|max:10',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'fields' => 'required|array|min:1',
            'fields.*.type' => 'required|in:text,email,number,select,radio,checkbox,textarea,file,date,phone,url',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.name' => 'required|string|max:100|regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
            'fields.*.required' => 'boolean',
            'fields.*.order' => 'required|integer|min:1',
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string|max:255',
            'fields.*.validation_rules' => 'nullable|string|max:500',
            'fields.*.help_text' => 'nullable|string|max:500',
            'terms_and_conditions' => 'nullable|string|max:5000',
            'template_id' => 'nullable|exists:form_templates,id',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del formulario es obligatorio.',
            'version.required' => 'La versión es obligatoria.',
            'fields.required' => 'El formulario debe tener al menos un campo.',
            'fields.*.type.required' => 'El tipo de campo es obligatorio.',
            'fields.*.type.in' => 'El tipo de campo no es válido.',
            'fields.*.label.required' => 'La etiqueta del campo es obligatoria.',
            'fields.*.name.required' => 'El nombre del campo es obligatorio.',
            'fields.*.name.regex' => 'El nombre del campo debe ser un identificador válido.',
            'fields.*.order.required' => 'El orden del campo es obligatorio.',
            'fields.*.order.min' => 'El orden del campo debe ser al menos 1.',
            'template_id.exists' => 'La plantilla seleccionada no existe.',
        ];
    }
}
