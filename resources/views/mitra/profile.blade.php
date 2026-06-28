@extends('layouts.dashboard')
@section('title', 'Profil dan Dokumen Mitra')
@section('page-title', 'Profil, Rekening, dan Dokumen')
@section('page-subtitle', 'Lengkapi identitas usaha agar admin dapat memverifikasi toko rental kamera Anda.')
@section('content')
@php
    $documentLabels = ['ktp' => 'KTP Pemilik', 'nib' => 'NIB', 'rekening' => 'Bukti Rekening', 'foto_usaha' => 'Foto Usaha', 'npwp' => 'NPWP', 'sku' => 'SKU'];
    $latestDocuments = $partner->documents->sortByDesc('id')->groupBy('document_type')->map->first();
@endphp
<div class="grid items-start gap-6 xl:grid-cols-[1fr_390px]">
    <div class="space-y-6">
        <form action="{{ route('mitra.profile.update') }}" method="POST" class="card p-6">
            @csrf @method('PATCH')
            <h2 class="text-lg font-extrabold text-ink">Informasi usaha</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div><label class="label">Nama toko rental</label><input name="business_name" value="{{ old('business_name', $partner->business_name) }}" class="input" required></div>
                <div><label class="label">Jenis usaha</label><input name="business_type" value="{{ old('business_type', $partner->business_type) }}" class="input" placeholder="Rental kamera dan perlengkapan produksi"></div>
                <div><label class="label">Nama pemilik</label><input name="owner_name" value="{{ old('owner_name', $partner->owner_name) }}" class="input" required></div>
                <div><label class="label">Nomor WhatsApp toko</label><input name="phone" value="{{ old('phone', $partner->phone) }}" class="input" required></div>
                <div><label class="label">Email usaha</label><input type="email" name="business_email" value="{{ old('business_email', $partner->business_email) }}" class="input" required></div>
                <div><label class="label">NPWP / nomor pajak</label><input name="tax_number" value="{{ old('tax_number', $partner->tax_number) }}" class="input"></div>
                <div><label class="label">Kota</label><input name="city" value="{{ old('city', $partner->city) }}" class="input" required></div>
                <div><label class="label">Provinsi</label><input name="province" value="{{ old('province', $partner->province) }}" class="input" required></div>
                <div class="sm:col-span-2"><label class="label">Alamat lengkap toko</label><textarea name="address" rows="3" class="input" required>{{ old('address', $partner->address) }}</textarea></div>
                <div class="sm:col-span-2"><label class="label">Jam operasional</label><input name="operational_hours" value="{{ old('operational_hours', $partner->operational_hours) }}" class="input" placeholder="Senin-Sabtu, 08.00-20.00 WIB" required></div>
                <div class="sm:col-span-2"><label class="label">Catatan pengambilan</label><textarea name="pickup_note" rows="3" class="input" required>{{ old('pickup_note', $partner->pickup_note) }}</textarea></div>
                <div class="sm:col-span-2"><label class="label">Deskripsi usaha</label><textarea name="description" rows="4" class="input">{{ old('description', $partner->description) }}</textarea></div>
            </div>
            <h2 class="mt-8 border-t border-slate-100 pt-7 text-lg font-extrabold text-ink">Rekening pencairan</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                <div><label class="label">Nama bank</label><input name="bank_name" value="{{ old('bank_name', $partner->bank_name) }}" class="input" required></div>
                <div><label class="label">Nomor rekening</label><input name="bank_account_number" value="{{ old('bank_account_number', $partner->bank_account_number) }}" class="input" required></div>
                <div><label class="label">Nama pemilik rekening</label><input name="bank_account_holder" value="{{ old('bank_account_holder', $partner->bank_account_holder) }}" class="input" required></div>
            </div>
            <button class="btn-primary mt-6">Simpan profil mitra</button>
        </form>

        <section class="card p-6">
            <div class="flex items-center justify-between"><div><h2 class="font-extrabold text-ink">Dokumen terkirim</h2><p class="mt-1 text-xs text-slate-500">Versi terbaru setiap jenis dokumen digunakan dalam peninjauan.</p></div><x-status-badge :status="$partner->verification_status" /></div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach($documentLabels as $type => $label)
                    @php $document = $latestDocuments->get($type); @endphp
                    <div class="rounded-xl border p-4 {{ $document ? 'border-slate-200' : 'border-dashed border-amber-200 bg-amber-50/40' }}">
                        <div class="flex items-center justify-between gap-2"><p class="text-sm font-bold text-ink">{{ $label }}</p>@if($document)<x-status-badge :status="$document->status" />@endif</div>
                        @if($document)<p class="mt-2 text-xs text-slate-400">{{ $document->document_name }} · {{ $document->created_at->translatedFormat('d M Y') }}</p><a href="{{ route('partners.documents.show', $document) }}" class="mt-3 inline-flex text-xs font-bold text-indigo-600">Buka dokumen →</a>@else<p class="mt-2 text-xs text-amber-700">Belum diunggah.</p>@endif
                    </div>
                @endforeach
            </div>
            @if($partner->admin_notes)<p class="mt-5 rounded-xl bg-rose-50 p-4 text-sm text-rose-700"><strong>Catatan admin:</strong> {{ $partner->admin_notes }}</p>@endif
        </section>
    </div>

    <aside class="card p-5 xl:sticky xl:top-24">
        <h2 class="font-extrabold text-ink">Unggah dokumen</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">KTP, NIB, rekening, dan foto usaha wajib disetujui. File disimpan privat.</p>
        <form action="{{ route('mitra.documents.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf
            <div><label class="label">Jenis dokumen</label><select name="document_type" class="input">@foreach($documentLabels as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach</select></div>
            <div><label class="label">Nama dokumen</label><input name="document_name" class="input" placeholder="Contoh: NIB Lensaku Rental 2026" required></div>
            <div><label class="label">Nomor dokumen</label><input name="document_number" class="input" placeholder="Nomor dokumen jika tersedia"></div>
            <div class="grid grid-cols-2 gap-3"><div><label class="label">Tanggal terbit</label><input type="date" name="issued_at" class="input px-3"></div><div><label class="label">Berlaku hingga</label><input type="date" name="expires_at" class="input px-3"></div></div>
            <div><label class="label">Berkas</label><input type="file" name="file" class="input" accept=".jpg,.jpeg,.png,.pdf" required><p class="mt-2 text-xs text-slate-400">JPG, PNG, atau PDF maksimal 5 MB.</p></div>
            <button class="btn-primary w-full">Kirim untuk ditinjau</button>
        </form>
    </aside>
</div>
@endsection
