@extends('layouts.dashboard')
@section('title', 'Tinjau '.$partner->business_name)
@section('page-title', 'Detail Peninjauan Mitra')
@section('page-subtitle', 'Validasi identitas, legalitas, rekening, dan kesiapan operasional.')
@section('content')
@php
    $documentLabels = [
        'ktp' => 'KTP pemilik',
        'nib' => 'Nomor Induk Berusaha',
        'rekening' => 'Bukti rekening usaha',
        'foto_usaha' => 'Foto lokasi usaha',
        'npwp' => 'NPWP',
        'sku' => 'Surat Keterangan Usaha',
    ];
    $latestDocuments = $partner->documents->sortByDesc('id')->groupBy('document_type')->map->first();
    $allRequiredApproved = collect($requiredDocuments)->every(fn ($type) => $latestDocuments->get($type)?->status === 'approved');
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ route('admin.partners.index') }}" class="text-sm font-bold text-indigo-600">← Daftar mitra</a>
        <div class="mt-2 flex flex-wrap items-center gap-3"><h2 class="text-2xl font-black text-ink">{{ $partner->business_name }}</h2><x-status-badge :status="$partner->verification_status" /></div>
        <p class="mt-1 text-sm text-slate-500">Diajukan {{ $partner->created_at->translatedFormat('d F Y, H:i') }}</p>
    </div>
    <div class="rounded-2xl border px-4 py-3 {{ $allRequiredApproved ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
        <p class="text-xs font-bold uppercase tracking-wider">Kesiapan verifikasi</p>
        <p class="mt-1 text-sm font-extrabold">{{ $allRequiredApproved ? 'Semua dokumen wajib disetujui' : 'Masih ada dokumen yang perlu ditinjau' }}</p>
    </div>
</div>

<div class="grid items-start gap-6 xl:grid-cols-[1fr_380px]">
    <div class="space-y-6">
        <section class="card p-6">
            <h3 class="font-extrabold text-ink">Profil usaha dan penanggung jawab</h3>
            <div class="mt-5 grid gap-5 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Nama usaha</span><strong class="text-ink">{{ $partner->business_name }}</strong></p>
                <p><span class="block text-xs text-slate-400">Jenis usaha</span>{{ $partner->business_type }}</p>
                <p><span class="block text-xs text-slate-400">Pemilik / penanggung jawab</span>{{ $partner->owner_name }}</p>
                <p><span class="block text-xs text-slate-400">Email akun</span>{{ $partner->user->email }}</p>
                <p><span class="block text-xs text-slate-400">Email usaha</span>{{ $partner->business_email ?: '-' }}</p>
                <p><span class="block text-xs text-slate-400">WhatsApp</span>{{ $partner->phone }}</p>
                <p><span class="block text-xs text-slate-400">NPWP / nomor pajak</span>{{ $partner->tax_number ?: '-' }}</p>
                <p><span class="block text-xs text-slate-400">Jam operasional</span>{{ $partner->operational_hours }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat lengkap</span>{{ $partner->address }}, {{ $partner->city }}, {{ $partner->province }} {{ $partner->postal_code }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Deskripsi usaha</span>{{ $partner->description }}</p>
            </div>
        </section>

        <section class="card p-6">
            <h3 class="font-extrabold text-ink">Rekening pencairan</h3>
            <div class="mt-5 grid gap-5 text-sm sm:grid-cols-3">
                <p><span class="block text-xs text-slate-400">Bank</span><strong class="text-ink">{{ $partner->bank_name ?: '-' }}</strong></p>
                <p><span class="block text-xs text-slate-400">Nomor rekening</span>{{ $partner->bank_account_number ?: '-' }}</p>
                <p><span class="block text-xs text-slate-400">Pemilik rekening</span>{{ $partner->bank_account_holder ?: '-' }}</p>
            </div>
        </section>

        <section class="card p-6">
            <div class="flex items-center justify-between gap-3"><div><h3 class="font-extrabold text-ink">Kelengkapan dokumen</h3><p class="mt-1 text-xs text-slate-500">Tinjau dokumen satu per satu sebelum memverifikasi mitra.</p></div><span class="text-sm font-bold text-slate-500">{{ $partner->documents->count() }} berkas</span></div>
            <div class="mt-5 space-y-4">
                @foreach(array_keys($documentLabels) as $type)
                    @php $document = $latestDocuments->get($type); @endphp
                    <article class="rounded-2xl border {{ $document ? 'border-slate-200' : 'border-dashed border-rose-200 bg-rose-50/40' }} p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2"><h4 class="font-extrabold text-ink">{{ $documentLabels[$type] }}</h4>@if(in_array($type, $requiredDocuments))<span class="rounded bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-600">WAJIB</span>@endif @if($document)<x-status-badge :status="$document->status" />@endif</div>
                                @if($document)
                                    <div class="mt-3 grid gap-2 text-xs text-slate-500 sm:grid-cols-3">
                                        <p><span class="block text-slate-400">Nama berkas</span>{{ $document->document_name }}</p>
                                        <p><span class="block text-slate-400">Nomor</span>{{ $document->document_number ?: '-' }}</p>
                                        <p><span class="block text-slate-400">Berlaku hingga</span>{{ $document->expires_at?->translatedFormat('d M Y') ?: 'Tidak terbatas' }}</p>
                                    </div>
                                    @if($document->admin_notes)<p class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600"><strong>Catatan:</strong> {{ $document->admin_notes }}</p>@endif
                                @else
                                    <p class="mt-2 text-sm text-rose-600">Dokumen belum diunggah oleh mitra.</p>
                                @endif
                            </div>
                            @if($document)
                                <div class="flex flex-wrap gap-2 lg:w-72">
                                    <a href="{{ route('partners.documents.show', $document) }}" class="btn-secondary flex-1 justify-center py-2">Buka berkas</a>
                                    @if($document->status === 'pending')
                                        <form action="{{ route('admin.partners.documents.update', [$partner, $document]) }}" method="POST" class="w-full space-y-2">
                                            @csrf @method('PATCH')
                                            <textarea name="admin_notes" class="input text-xs" rows="2" placeholder="Catatan pemeriksaan / alasan penolakan"></textarea>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button name="status" value="approved" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white">Setujui</button>
                                                <button name="status" value="rejected" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-bold text-white">Tolak</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="border-b border-slate-100 p-5"><h3 class="font-extrabold text-ink">Kamera yang diajukan</h3></div>
            <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Kamera</th><th>Kategori</th><th>Unit</th><th>Status</th></tr></thead><tbody>
                @forelse($partner->products as $product)
                    <tr><td><p class="font-bold text-ink">{{ $product->name }}</p><p class="text-xs text-slate-400">{{ $product->brand }} {{ $product->model }}</p></td><td>{{ $product->category->name }}</td><td>{{ $product->stock_total }}</td><td><x-status-badge :status="$product->status" /></td></tr>
                @empty
                    <tr><td colspan="4" class="py-10 text-center text-slate-400">Belum ada kamera.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </div>

    <aside class="space-y-6 xl:sticky xl:top-24">
        <section class="card p-6">
            <h3 class="font-extrabold text-ink">Keputusan verifikasi</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Persetujuan hanya dapat dilakukan setelah empat dokumen wajib berstatus disetujui.</p>
            <form action="{{ route('admin.partners.update', $partner) }}" method="POST" class="mt-5 space-y-3">
                @csrf @method('PATCH')
                <textarea name="admin_notes" class="input" rows="4" placeholder="Catatan keputusan untuk mitra">{{ old('admin_notes', $partner->admin_notes) }}</textarea>
                <button name="verification_status" value="verified" class="btn-primary w-full" @disabled(!$allRequiredApproved)>Verifikasi mitra</button>
                <button name="verification_status" value="rejected" class="btn-danger w-full">Tolak pengajuan</button>
                @if($partner->verification_status === 'verified')<button name="verification_status" value="suspended" class="btn-secondary w-full">Tangguhkan mitra</button>@endif
            </form>
        </section>
        <section class="card p-5">
            <h3 class="font-extrabold text-ink">Ringkasan aktivitas</h3>
            <div class="mt-4 grid grid-cols-2 gap-3 text-center"><div class="rounded-xl bg-slate-50 p-3"><p class="text-2xl font-black text-ink">{{ $partner->products_count }}</p><p class="text-xs text-slate-400">Kamera</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="text-2xl font-black text-ink">{{ $partner->bookings_count }}</p><p class="text-xs text-slate-400">Transaksi</p></div></div>
        </section>
    </aside>
</div>
@endsection
