<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'mitra';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', 'exists:categories,id'],
            'product_type' => ['required', 'in:rental'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:120'],
            'camera_type' => ['required', 'string', 'max:80'],
            'sensor_type' => ['nullable', 'string', 'max:100'],
            'resolution_mp' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'video_resolution' => ['nullable', 'string', 'max:100'],
            'lens_mount' => ['nullable', 'string', 'max:80'],
            'condition_label' => ['required', 'string', 'max:80'],
            'included_accessories' => ['required', 'string', 'max:3000'],
            'rental_terms' => ['required', 'string', 'max:3000'],
            'description' => ['required', 'string', 'min:20'],
            'price' => ['required', 'numeric', 'min:1000'],
            'security_deposit' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'replacement_value' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['required', 'in:day'],
            'stock_total' => ['required', 'integer', 'min:1'],
            'min_rent_duration' => ['required', 'integer', 'min:1'],
            'location_city' => ['required', 'string', 'max:100'],
            'location_address' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:3072'],
            'submit_review' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'security_deposit.lte' => 'Deposit maksimal sama dengan harga sewa satu hari.',
        ];
    }
}
