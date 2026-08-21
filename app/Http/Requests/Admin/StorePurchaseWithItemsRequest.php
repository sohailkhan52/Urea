<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseWithItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('purchases.create');
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Parse items JSON if it's a string
        if (is_string($this->items)) {
            try {
                $items = json_decode($this->items, true);
                if (is_array($items) && count($items) > 0) {
                    $this->merge(['items' => $items]);
                }
            } catch (\Exception $e) {
                // Keep original if parsing fails
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Purchase header
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            
            // Purchase expenses
            'discount' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'other_expenses' => 'nullable|numeric|min:0',
            
            // Payment
            'paid_amount' => 'nullable|numeric|min:0',
            
            // Purchase items (required, must have at least 1 item)
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.discount' => 'nullable|numeric|min:0',
            
            // Action type (confirm)
            'action' => 'required|in:confirm',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Please select a supplier.',
            'supplier_id.exists' => 'The selected supplier is invalid.',
            'warehouse_id.required' => 'Please select a warehouse.',
            'warehouse_id.exists' => 'The selected warehouse is invalid.',
            'purchase_date.required' => 'Purchase date is required.',
            'purchase_date.date' => 'Purchase date must be a valid date.',
            'items.required' => 'Please add at least one item to the purchase.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'Please add at least one item to the purchase.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.integer' => 'Product ID must be an integer.',
            'items.*.product_id.exists' => 'The selected product is invalid.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.numeric' => 'Quantity must be a number.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.numeric' => 'Unit price must be a number.',
            'items.*.unit_price.min' => 'Unit price must be greater than 0.',
            'action.required' => 'Action is required.',
            'action.in' => 'Invalid action.',
        ];
    }
}
