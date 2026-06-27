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
            'product_type' => ['required', 'in:rental,sale,service'],
            'description' => ['required', 'string', 'min:20'],
            'price' => ['required', 'numeric', 'min:1000'],
            'price_unit' => ['required', 'in:hour,day,week,month,service,item'],
            'stock_total' => ['required', 'integer', 'min:1'],
            'min_rent_duration' => ['required', 'integer', 'min:1'],
            'location_city' => ['required', 'string', 'max:100'],
            'location_address' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:3072'],
            'submit_review' => ['nullable', 'boolean'],
        ];
    }
}
