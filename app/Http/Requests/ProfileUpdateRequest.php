<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone'                   => ['required', 'digits_between:10,12'],
            'date_of_birth'           => ['nullable', 'date', 'before:today'],
            'gender'                  => ['nullable', 'in:male,female'],
            'profession'              => ['nullable', 'string', 'max:120'],
            'address'                 => ['required_if:role,customer', 'nullable', 'string', 'max:2000'],
            'city'                    => ['required_if:role,customer', 'nullable', 'string', 'max:100'],
            'province'                => ['required_if:role,customer', 'nullable', 'string', 'max:100'],
            'postal_code'             => ['nullable', 'string', 'max:20'],
            'identity_type'           => ['required_if:role,customer', 'nullable', 'in:ktp,sim,kartu_pelajar,paspor'],
            'identity_number'         => ['required_if:role,customer', 'nullable', 'string', 'max:100'],
            'identity_file'           => [
                Rule::requiredIf(fn () => $this->user()->role === 'customer' && ! $this->user()->identity_file),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
            'avatar'                  => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'digits_between:10,12'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['role' => $this->user()->role]);
    }
}