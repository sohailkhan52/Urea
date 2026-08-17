<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->can('transfers.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'source_warehouse_id' => [
                'required',
                'exists:warehouses,id',
                function ($attribute, $value, $fail) {
                    if ($value === $this->input('destination_warehouse_id')) {
                        $fail('Source and destination warehouses cannot be the same.');
                    }
                },
            ],
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'transfer_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'source_warehouse_id.required' => 'Source warehouse is required.',
            'source_warehouse_id.exists' => 'Selected source warehouse does not exist.',
            'destination_warehouse_id.required' => 'Destination warehouse is required.',
            'destination_warehouse_id.exists' => 'Selected destination warehouse does not exist.',
            'transfer_date.required' => 'Transfer date is required.',
            'transfer_date.date' => 'Transfer date must be a valid date.',
            'transfer_date.after_or_equal' => 'Transfer date cannot be in the past.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
