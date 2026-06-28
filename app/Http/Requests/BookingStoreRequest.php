<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'regex:/^[0-9+\\-\\s()]{8,20}$/'],
            'customer_email' => ['required', 'email:rfc', 'max:150'],
            'customer_address' => ['required', 'string', 'max:2000'],
            'identity_number' => ['required', 'string', 'max:100'],
            'identity_file' => [
                Rule::requiredIf(fn () => ! $this->user()?->identity_file),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'customer_phone.regex' => 'Format nomor HP/WhatsApp tidak valid.',
            'customer_email.required' => 'Email wajib diisi.',
            'customer_email.email' => 'Format email tidak valid.',
            'customer_address.required' => 'Alamat lengkap wajib diisi.',
            'identity_number.required' => 'Nomor identitas wajib diisi.',
            'identity_file.required' => 'Foto atau dokumen identitas wajib diunggah.',
            'identity_file.mimes' => 'Identitas hanya boleh berupa JPG, PNG, atau PDF.',
            'identity_file.max' => 'Ukuran file identitas maksimal 2 MB.',
            'quantity.min' => 'Jumlah unit disewa minimal 1.',
        ];
    }
}
