<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'government_id' => 'sometimes|string|max:11',
            'email' => 'sometimes|string|email|max:100',
            'name' => 'sometimes|string|max:200',
            'amount' => 'sometimes|numeric|between:0,9999999999999999999999999999999999999999999999999999999999999.999999999999999999999999999999',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|string|max:20',
        ];
    }
}
