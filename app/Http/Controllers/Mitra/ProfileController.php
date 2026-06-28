<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('mitra.profile', ['partner' => auth()->user()->partnerProfile->load('documents')]);
    }

    public function update()
    {
        $data = request()->validate([
            'business_name' => ['required', 'string', 'max:180'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'owner_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'business_email' => ['required', 'email', 'max:150'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'regex:/^[0-9]{6,30}$/'],
            'bank_account_holder' => ['required', 'string', 'max:150'],
            'operational_hours' => ['required', 'string', 'max:180'],
            'pickup_note' => ['required', 'string', 'max:1000'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        auth()->user()->partnerProfile->update($data);

        return back()->with('success', 'Profil usaha berhasil diperbarui.');
    }

    public function uploadDocument()
    {
        $data = request()->validate([
            'document_type' => ['required', 'in:ktp,nib,sku,npwp,rekening,foto_usaha'],
            'document_name' => ['required', 'string', 'max:150'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:issued_at'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        $data['file_path'] = request()->file('file')->store('partner-documents');
        $data['is_required'] = in_array($data['document_type'], ['ktp', 'nib', 'rekening', 'foto_usaha']);
        unset($data['file']);
        $partner = auth()->user()->partnerProfile;
        $partner->documents()->create($data + ['status' => 'pending']);
        if ($data['is_required'] && $partner->verification_status === 'verified') {
            $partner->update([
                'verification_status' => 'pending',
                'verified_at' => null,
                'admin_notes' => 'Dokumen wajib diperbarui dan perlu ditinjau ulang.',
            ]);
        }

        return back()->with('success', 'Dokumen berhasil dikirim untuk diverifikasi.');
    }
}
