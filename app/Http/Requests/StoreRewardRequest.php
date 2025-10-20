<?php

namespace App\Http\Requests;

class StoreRewardRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'cost' => 'required|integer|min:1|max:999999',
            'category' => 'required|string|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_digital' => 'boolean',
            'expiration_days' => 'nullable|integer|min:1|max:365',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la recompensa es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'cost.required' => 'El costo en puntos es obligatorio.',
            'cost.min' => 'El costo debe ser al menos 1 punto.',
            'cost.max' => 'El costo no puede exceder 999,999 puntos.',
            'category.required' => 'La categoría es obligatoria.',
            'stock_quantity.min' => 'La cantidad en stock no puede ser negativa.',
            'expiration_days.min' => 'Los días de expiración deben ser al menos 1.',
            'expiration_days.max' => 'Los días de expiración no pueden exceder 365.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.max' => 'La imagen no debe ser mayor a 2MB.',
        ];
    }
}
