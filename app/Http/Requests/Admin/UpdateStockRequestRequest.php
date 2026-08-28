<?php

namespace App\Http\Requests\Admin;

use App\Models\StockRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $request = $this->route('stock_request');
        
        if (!$request) {
            return false;
        }

        // Check if user can access this request's warehouse
        return auth()->user()->canAccessWarehouse($request->warehouse_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'priority' => [
                'sometimes',
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
            'priority' => 'priority',
            'reason' => 'reason',
            'notes' => 'notes',
        ];
    }
}
