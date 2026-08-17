<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'company_id' => ['required', 'exists:companies,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku', 'alpha_dash'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'bag_weight' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'weight_unit' => ['required', 'string', Rule::in(['KG', 'LB', 'TON'])],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999999.99'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:99999999999.99', 'gte:purchase_price'],
            'minimum_stock_level' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
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
            'company_id' => 'company',
            'category_id' => 'category',
            'sku' => 'SKU',
            'bag_weight' => 'bag weight',
            'weight_unit' => 'weight unit',
            'purchase_price' => 'purchase price',
            'sale_price' => 'sale price',
            'minimum_stock_level' => 'minimum stock level',
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
            'sku.alpha_dash' => 'The SKU may only contain letters, numbers, dashes, and underscores.',
            'sku.unique' => 'This SKU is already in use.',
            'barcode.unique' => 'This barcode is already in use.',
            'sale_price.gte' => 'The sale price must be greater than or equal to purchase price.',
        ];
    }
}
