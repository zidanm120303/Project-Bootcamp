@extends('layouts.dashboard')
@section('title', 'Moderasi Kamera')
@section('page-title', 'Kamera dan Peralatan')
@section('page-subtitle', 'Tinjau spesifikasi dan kelayakan kamera sebelum tampil di marketplace.')
@section('content')
@php $labels = ['pending_review' => 'Menunggu tinjauan', 'active' => 'Aktif', 'draft' => 'Draf', 'rejected' => 'Ditolak', 'inactive' => 'Nonaktif', 'archived' => 'Diarsipkan']; @endphp
<form class="mb-6 grid gap-3 sm:grid-cols-[1fr_230px_auto]"><input name="q" value="{{ request('q') }}" class="input" placeholder="Cari kamera, merek, atau model..."><select name="status" class="input"><option value="">Semua status</option>@foreach($labels as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select><button class="btn-primary">Terapkan</button></form>
<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($products as $product)
        <article class="card overflow-hidden">
            <img src="{{ $product->image_url }}" class="aspect-[16/8] w-full object-cover" alt="">
            <div class="p-5">
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-indigo-600">{{ $product->category->name }}</span><x-status-badge :status="$product->status" /></div>
                <h2 class="mt-3 font-black text-ink">{{ $product->name }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $product->brand }} {{ $product->model }} · {{ $product->condition_label }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $product->partner->business_name }} · {{ $product->location_city }}</p>
                <div class="mt-4 flex items-end justify-between"><p class="text-lg font-black text-ink">Rp{{ number_format($product->price, 0, ',', '.') }}<span class="text-xs font-medium text-slate-400">/hari</span></p><span class="text-xs font-bold text-slate-500">{{ $product->stock_total }} unit</span></div>
                <a href="{{ route('admin.products.show', $product) }}" class="btn-primary mt-4 w-full justify-center">Tinjau detail →</a>
            </div>
        </article>
    @empty
        <div class="sm:col-span-2 xl:col-span-3"><x-empty-state title="Kamera tidak ditemukan" description="Tidak ada data yang sesuai dengan filter." /></div>
    @endforelse
</div>
<div class="mt-6">{{ $products->links() }}</div>
@endsection
