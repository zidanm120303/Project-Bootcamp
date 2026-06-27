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
            'phone' => ['required', 'string', 'max:30'],
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
            'document_number' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        $data['file_path'] = request()->file('file')->store('partner-documents', 'public');
        unset($data['file']);
        auth()->user()->partnerProfile->documents()->create($data + ['status' => 'pending']);

        return back()->with('success', 'Dokumen berhasil dikirim untuk diverifikasi.');
    }
}
