@extends('layouts.dashboard')
@section('title', 'Peninjauan Mitra')
@section('page-title', 'Peninjauan Mitra')
@section('page-subtitle', 'Periksa profil usaha, legalitas, rekening, dan dokumen toko rental kamera.')
@section('content')
@php $statusLabels = ['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak', 'suspended' => 'Ditangguhkan']; @endphp
<form class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto]"><input name="q" value="{{ request('q') }}" class="input" placeholder="Cari toko, pemilik, atau email..."><select name="status" class="input"><option value="">Semua status</option>@foreach($statusLabels as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select><button class="btn-primary">Terapkan</button></form>

<div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
    @forelse($partners as $partner)
        @php
            $latestDocs = $partner->documents->sortByDesc('id')->groupBy('document_type')->map->first();
            $required = collect(['ktp', 'nib', 'rekening', 'foto_usaha']);
            $complete = $required->filter(fn ($type) => $latestDocs->get($type)?->status === 'approved')->count();
        @endphp
        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-100/60">
            <div class="relative h-32 bg-gradient-to-br from-indigo-600 via-blue-600 to-sky-400 p-5 text-white">
                <div class="absolute -right-8 -top-12 h-36 w-36 rounded-full bg-white/10"></div>
                <div class="relative flex items-start justify-between"><span class="grid h-14 w-14 place-items-center rounded-2xl bg-white/20 text-xl font-black backdrop-blur">{{ str($partner->business_name)->substr(0,1) }}</span><x-status-badge :status="$partner->verification_status" /></div>
                <p class="relative mt-3 truncate text-lg font-black">{{ $partner->business_name }}</p>
            </div>
            <div class="p-5">
                <p class="text-sm font-semibold text-slate-600">{{ $partner->business_type }}</p>
                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400"><x-icon name="location" class="h-4 w-4" />{{ $partner->city }}, {{ $partner->province }}</p>
                <div class="mt-4 grid grid-cols-3 gap-2 text-center"><div class="rounded-xl bg-slate-50 p-3"><p class="font-black text-ink">{{ $partner->products_count }}</p><p class="text-[10px] text-slate-400">Kamera</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="font-black text-ink">{{ $partner->bookings_count }}</p><p class="text-[10px] text-slate-400">Transaksi</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="font-black text-ink">{{ $complete }}/4</p><p class="text-[10px] text-slate-400">Dokumen</p></div></div>
                <div class="mt-4"><div class="flex justify-between text-[11px]"><span class="text-slate-400">Kelengkapan dokumen wajib</span><strong class="{{ $complete === 4 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $complete * 25 }}%</strong></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $complete === 4 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $complete * 25 }}%"></div></div></div>
                <div class="mt-4 flex flex-wrap gap-1.5">@foreach($required as $type)<span class="rounded-lg px-2 py-1 text-[10px] font-bold uppercase {{ $latestDocs->get($type)?->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ str($type)->replace('_',' ') }} {{ $latestDocs->get($type)?->status === 'approved' ? '✓' : '!' }}</span>@endforeach</div>
                <a href="{{ route('admin.partners.show', $partner) }}" class="btn-primary mt-5 w-full justify-center">Tinjau detail →</a>
            </div>
        </article>
    @empty
        <div class="md:col-span-2 2xl:col-span-3"><x-empty-state title="Mitra tidak ditemukan" description="Tidak ada pengajuan yang sesuai dengan filter." /></div>
    @endforelse
</div>
<div class="mt-7">{{ $partners->links() }}</div>
@endsection
