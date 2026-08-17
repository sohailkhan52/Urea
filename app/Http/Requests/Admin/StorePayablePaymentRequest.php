<?php

namespace App\Http\Requests\Admin;

use App\Models\PurchasePayment;
use Illuminate\Foundation\Http\FormRequest;

class StorePayablePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        return $this->user()->hasPermission('payables.create') || 
               $this->user()->hasPermission('payables.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'purchase_id' => 'required|exists:purchases,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:' . implode(',', array_keys(PurchasePayment::$methods)),
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
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
            'purchase_id' => 'Purchase',
            'amount' => 'Payment Amount',
            'payment_method' => 'Payment Method',
            'payment_date' => 'Payment Date',
        ];
    }
}
