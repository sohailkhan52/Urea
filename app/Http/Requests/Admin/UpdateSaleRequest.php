<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('sales.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => [
                'nullable',
                'exists:customers,id',
            ],
            'walkin_customer_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'walkin_customer_contact' => [
                'nullable',
                'string',
                'max:50',
            ],
            'sale_date' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sale_date.required' => 'Sale date is required.',
            'sale_date.date' => 'Sale date must be a valid date.',
            'customer_id.exists' => 'Invalid customer selected.',
            'walkin_customer_name.max' => 'Walk-in customer name cannot exceed 100 characters.',
            'walkin_customer_contact.max' => 'Walk-in customer contact cannot exceed 50 characters.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
