<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'start_at' => ['required', 'date', 'after_or_equal:today'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'qty' => ['required', 'integer', 'min:1'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
