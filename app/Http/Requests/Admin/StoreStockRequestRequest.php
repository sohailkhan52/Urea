<?php

namespace App\Http\Requests\Admin;

use App\Models\StockRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => [
                'required',
                'exists:warehouses,id',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    
                    // Super admin can select any warehouse
                    if ($user->isSuperAdmin()) {
                        return;
                    }
                    
                    // Regular admin can only use their assigned warehouse
                    if (!$user->canAccessWarehouse($value)) {
                        $fail('You do not have permission to create requests for this warehouse.');
                    }
                },
            ],
            'priority' => [
                'required',
                'in:' . implode(',', array_keys(StockRequest::getPriorities())),
            ],
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'warehouse_id' => 'warehouse',
            'priority' => 'priority',
            'reason' => 'reason',
            'notes' => 'notes',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Please select a warehouse.',
            'warehouse_id.exists' => 'The selected warehouse is invalid.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'The selected priority is invalid.',
        ];
    }
}
