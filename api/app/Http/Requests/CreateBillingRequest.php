<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateBillingRequest extends FormRequest
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
            'id' => 'required|string|max:40|unique:billings,id',
            'government_id' => 'required|string|max:11',
            'email' => 'required|string|email|max:100',
            'name' => 'required|string|max:200',
            'amount' => 'required|numeric|between:0,9999999999999999999999999999999999999999999999999999999999999.999999999999999999999999999999',
            'due_date' => 'required|date',
            'status' => 'required|string|max:20',
        ];
    }
}
