<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('customers.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_type' => [
                'required',
                Rule::in(array_keys(Customer::$types)),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'father_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'cnic' => [
                'nullable',
                'string',
                'max:50',
                'unique:customers,cnic',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:customers,email',
            ],
            'village' => [
                'nullable',
                'string',
                'max:255',
            ],
            'city' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'credit_limit' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_type.required' => 'Customer type is required.',
            'customer_type.in' => 'Invalid customer type selected.',
            'name.required' => 'Customer name is required.',
            'name.max' => 'Customer name cannot exceed 255 characters.',
            'cnic.unique' => 'This CNIC is already registered.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'address.max' => 'Address cannot exceed 1000 characters.',
            'credit_limit.numeric' => 'Credit limit must be a valid number.',
            'credit_limit.min' => 'Credit limit cannot be negative.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Uppercase CNIC
        if ($this->cnic) {
            $this->merge([
                'cnic' => strtoupper($this->cnic),
            ]);
        }

        // Set default credit limit to 0 if empty
        if (blank($this->credit_limit)) {
            $this->merge([
                'credit_limit' => 0,
            ]);
        }
    }
}
