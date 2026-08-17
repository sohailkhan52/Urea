<?php

namespace App\Http\Requests\Admin;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code', 'alpha_dash'],
            'type' => ['required', Rule::in([
                Warehouse::TYPE_MAIN,
                Warehouse::TYPE_BRANCH,
                Warehouse::TYPE_STORE,
            ])],
            'address' => ['required', 'string', 'max:500'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in([Warehouse::STATUS_ACTIVE, Warehouse::STATUS_INACTIVE])],
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
            'branch_id' => 'branch',
            'code' => 'warehouse code',
            'manager_id' => 'manager',
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
        ];
    }
}
