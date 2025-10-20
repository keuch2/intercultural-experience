<?php

namespace App\Http\Requests;

class UpdateProfileRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $commonRules = $this->getCommonRules();
        $userId = auth()->id();
        
        return [
            'name' => $commonRules['name'],
            'email' => $commonRules['email'] . "|unique:users,email,{$userId}",
            'phone' => $commonRules['phone'],
            'nationality' => $commonRules['nationality'],
            'birth_date' => $commonRules['birth_date'],
            'address' => $commonRules['address'],
            'bank_info' => 'nullable|array',
            'bank_info.bank_name' => 'required_with:bank_info|string|max:255',
            'bank_info.account_number' => 'required_with:bank_info|string|max:50|regex:/^[0-9\-]+$/',
            'bank_info.routing_number' => 'required_with:bank_info|string|max:20|regex:/^[0-9\-]+$/',
        ];
    }
}
