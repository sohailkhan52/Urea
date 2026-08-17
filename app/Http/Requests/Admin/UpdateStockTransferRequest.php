<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->can('transfers.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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
            'transfer_date.required' => 'Transfer date is required.',
            'transfer_date.date' => 'Transfer date must be a valid date.',
            'transfer_date.after_or_equal' => 'Transfer date cannot be in the past.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
