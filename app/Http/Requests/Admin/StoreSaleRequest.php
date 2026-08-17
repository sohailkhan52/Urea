<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('sales.create');
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
            'warehouse_id' => [
                'required',
                'exists:warehouses,id',
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
            'warehouse_id.required' => 'Warehouse is required.',
            'warehouse_id.exists' => 'Invalid warehouse selected.',
            'sale_date.required' => 'Sale date is required.',
            'sale_date.date' => 'Sale date must be a valid date.',
            'customer_id.exists' => 'Invalid customer selected.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
