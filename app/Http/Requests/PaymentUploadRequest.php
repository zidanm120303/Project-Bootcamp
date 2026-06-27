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
        return ['proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']];
    }
}
