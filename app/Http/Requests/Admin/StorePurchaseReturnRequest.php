<?php

namespace App\Http\Requests\Admin;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('purchases.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purchase_id' => [
                'required',
                'exists:purchases,id',
                function ($attribute, $value, $fail) {
                    $purchase = Purchase::find($value);
                    
                    if (!$purchase) {
                        $fail('The selected purchase does not exist.');
                        return;
                    }
                    
                    if (!$purchase->isConfirmed()) {
                        $fail('Only confirmed purchases can be returned.');
                        return;
                    }
                    
                    if ($purchase->isCancelled()) {
                        $fail('Cancelled purchases cannot be returned.');
                        return;
                    }
                    
                    // Verify warehouse access
                    if (!$this->user()->canAccessWarehouse($purchase->warehouse_id)) {
                        $fail('You do not have permission to create returns for this purchase.');
                    }
                },
            ],
            'return_date' => 'required|date|before_or_equal:today',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
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
            'purchase_id.required' => 'Please select a purchase to return.',
            'purchase_id.exists' => 'The selected purchase does not exist.',
            'return_date.required' => 'Return date is required.',
            'return_date.before_or_equal' => 'Return date cannot be in the future.',
            'items.required' => 'Please select at least one item to return.',
            'items.min' => 'Please select at least one item to return.',
            'items.*.purchase_item_id.required' => 'Purchase item is required.',
            'items.*.purchase_item_id.exists' => 'The selected purchase item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}
