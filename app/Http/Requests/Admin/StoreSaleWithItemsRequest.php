<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleWithItemsRequest extends FormRequest
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
            'walkin_customer_name' => [
                'nullable',
                'string',
                'max:100',
                'required_without:customer_id',
            ],
            'walkin_customer_contact' => [
                'nullable',
                'string',
                'max:50',
            ],
            'warehouse_id' => [
                'required',
                'exists:warehouses,id',
            ],
            'sale_date' => [
                'required',
                'date',
            ],
            'family_id' => [
                'nullable',
                'exists:families,id',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'paid_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items' => [
                'required',
                'json',
            ],
            'items.*' => [
                'required',
                'array',
            ],
            'items.*.product_id' => [
                'required',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
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
            'family_id.exists' => 'Invalid family selected.',
            'walkin_customer_name.required_without' => 'Walk-in customer name is required when no customer is selected.',
            'walkin_customer_name.max' => 'Walk-in customer name cannot exceed 100 characters.',
            'walkin_customer_contact.max' => 'Walk-in customer contact cannot exceed 50 characters.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'discount.numeric' => 'Discount must be a valid number.',
            'discount.min' => 'Discount cannot be negative.',
            'paid_amount.numeric' => 'Paid amount must be a valid number.',
            'paid_amount.min' => 'Paid amount cannot be negative.',
            'items.required' => 'At least one product item is required.',
            'items.json' => 'Invalid items data.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'Invalid product selected.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.numeric' => 'Quantity must be a valid number.',
            'items.*.quantity.min' => 'Quantity must be at least 0.01.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.numeric' => 'Unit price must be a valid number.',
            'items.*.unit_price.min' => 'Unit price must be at least 0.01.',
            'items.*.discount.numeric' => 'Discount must be a valid number.',
            'items.*.discount.min' => 'Discount cannot be negative.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (blank($this->discount)) {
            $this->merge(['discount' => 0]);
        } else {
            $this->merge(['discount' => max(0, (float)$this->discount)]);
        }

        // Parse items if it's a string
        if (is_string($this->items)) {
            try {
                $decoded = json_decode($this->items, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded) && !empty($decoded)) {
                    // Keep items as JSON string for validation rule 'json'
                    // The validation will verify it's valid JSON
                    $this->merge(['items' => $this->items]);
                } else {
                    // Empty array or null, keep as is for validation to catch
                    $this->merge(['items' => $this->items]);
                }
            } catch (\Exception $e) {
                // Keep as is, let validation handle the error
                \Log::warning('Failed to parse items JSON', ['error' => $e->getMessage(), 'items' => $this->items]);
            }
        }

        // Ensure customer_id is null if not provided (not empty string)
        if ($this->filled('customer_id') && empty($this->customer_id)) {
            $this->merge(['customer_id' => null]);
        }
    }

    /**
     * Get the items array.
     */
    public function getItems(): array
    {
        $items = $this->input('items');
        
        if (is_string($items)) {
            try {
                $decoded = json_decode($items, true, 512, JSON_THROW_ON_ERROR);
                return is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                \Log::error('Failed to decode items JSON in getItems', ['error' => $e->getMessage(), 'items' => $items]);
                return [];
            }
        }

        return is_array($items) ? $items : [];
    }
}
