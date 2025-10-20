<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Common validation rules that can be reused
     */
    protected function getCommonRules(): array
    {
        return [
            'email' => 'required|string|email|max:255',
            'name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'phone' => 'nullable|string|max:50|regex:/^[\+]?[\d\s\-\(\)]+$/',
            'address' => 'nullable|string|max:500',
            'nationality' => 'nullable|string|max:100|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'birth_date' => 'nullable|date|before:today',
        ];
    }

    /**
     * Sanitize input data
     */
    protected function prepareForValidation()
    {
        $input = $this->all();
        
        // Sanitize string inputs
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
                $value = strip_tags($value);
            }
        });
        
        $this->replace($input);
    }
}
