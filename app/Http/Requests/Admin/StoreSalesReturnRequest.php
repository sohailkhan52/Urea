<?php

namespace App\Http\Requests\Admin;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesReturnRequest extends FormRequest
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
            'sale_id' => [
                'required',
                'exists:sales,id',
                function ($attribute, $value, $fail) {
                    $sale = Sale::find($value);
                    
                    if (!$sale) {
                        $fail('The selected sale does not exist.');
                        return;
                    }
                    
                    if (!$sale->isConfirmed()) {
                        $fail('Only confirmed sales can be returned.');
                        return;
                    }
                    
                    if ($sale->isCancelled()) {
                        $fail('Cancelled sales cannot be returned.');
                        return;
                    }
                    
                    // Verify warehouse access
                    if (!$this->user()->canAccessWarehouse($sale->warehouse_id)) {
                        $fail('You do not have permission to create returns for this sale.');
                    }
                },
            ],
            'return_date' => 'required|date|before_or_equal:today',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sale_id.required' => 'Please select a sale to return.',
            'sale_id.exists' => 'The selected sale does not exist.',
            'return_date.required' => 'Return date is required.',
            'return_date.before_or_equal' => 'Return date cannot be in the future.',
            'items.required' => 'Please select at least one item to return.',
            'items.min' => 'Please select at least one item to return.',
            'items.*.sale_item_id.required' => 'Sale item is required.',
            'items.*.sale_item_id.exists' => 'The selected sale item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}
