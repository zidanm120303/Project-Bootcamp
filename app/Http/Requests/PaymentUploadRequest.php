<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'sender_name' => ['required', 'string', 'max:150'],
            'sender_bank' => ['required', 'string', 'max:100'],
            'sender_account' => ['required', 'string', 'max:100'],
            'transfer_at' => ['required', 'date', 'before_or_equal:now'],
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
