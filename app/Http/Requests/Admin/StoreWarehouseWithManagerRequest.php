<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreWarehouseWithManagerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only super admins can create warehouses with managers
        return auth()->user()?->isSuperAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            // Warehouse Information
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code', 'alpha_dash'],
            'address' => ['required', 'string', 'max:500', 'min:5'],
            'status' => ['required', Rule::in(['active', 'inactive'])],

            // Warehouse Manager / Admin Information
            'admin_name' => ['required', 'string', 'max:255', 'min:3'],
            'admin_email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
            ],
            'admin_contact' => ['required', 'string', 'max:20', 'min:10', 'regex:/^[\d\-\+\s\(\)]+$/'],
            'admin_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                Password::default(),
            ],
            'admin_password_confirmation' => ['required', 'string'],
            'admin_profile_image' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'warehouse name',
            'code' => 'warehouse code',
            'address' => 'warehouse address',
            'status' => 'warehouse status',
            'admin_name' => 'manager name',
            'admin_email' => 'manager email',
            'admin_contact' => 'manager contact',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password confirmation',
            'admin_profile_image' => 'profile image',
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
            'code.alpha_dash' => 'The warehouse code may only contain letters, numbers, dashes, and underscores.',
            'code.unique' => 'This warehouse code is already in use.',
            'admin_email.unique' => 'This email address is already registered.',
            'admin_password.min' => 'Password must be at least 8 characters.',
            'admin_contact.regex' => 'Contact number must contain only digits, spaces, dashes, and parentheses.',
            'admin_profile_image.max' => 'Profile image must not exceed 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert contact to string if needed
        if (is_numeric($this->admin_contact)) {
            $this->merge([
                'admin_contact' => (string)$this->admin_contact,
            ]);
        }
    }
}
