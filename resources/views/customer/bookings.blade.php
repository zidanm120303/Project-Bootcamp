@extends('layouts.dashboard')
@section('title','Pesanan Saya')
@section('page-title','Pesanan Saya')
@section('page-subtitle','Riwayat dan status seluruh transaksi.')
@section('content')
<form class="mb-5 flex flex-wrap gap-3"><select name="status" class="input max-w-xs" onchange="this.form.submit()"><option value="">Semua status</option>@foreach(['pending','waiting_payment','paid','prepared','ongoing','completed','cancelled','disputed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select>@if(request('status'))<a href="{{ route('customer.bookings.index') }}" class="btn-secondary py-2">Reset</a>@endif</form>
@if($bookings->count())
<div class="space-y-4">
@foreach($bookings as $booking)
    <article class="card p-5">
        <div class="flex flex-col gap-5 md:flex-row md:items-center">
            <img src="{{ $booking->items->first()->product->image_url }}" class="h-24 w-full rounded-2xl object-cover md:w-28">
            <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><p class="text-xs font-bold text-indigo-600">{{ $booking->booking_code }}</p><x-status-badge :status="$booking->status" /></div><h2 class="mt-2 truncate text-lg font-black text-ink">{{ $booking->items->first()->product->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $booking->partner->business_name }} • {{ $booking->start_at->translatedFormat('d M') }}–{{ $booking->end_at->translatedFormat('d M Y') }}</p></div>
            <div class="flex items-center justify-between gap-5 border-t border-slate-100 pt-4 md:block md:border-0 md:pt-0 md:text-right"><div><p class="text-xs text-slate-400">Total pembayaran</p><p class="mt-1 text-lg font-black text-ink">Rp{{ number_format($booking->total_amount,0,',','.') }}</p></div><a href="{{ route('customer.bookings.show',$booking) }}" class="btn-secondary mt-0 py-2 md:mt-3">Lihat detail</a></div>
        </div>
    </article>
@endforeach
</div><div class="mt-6">{{ $bookings->links() }}</div>
@else<x-empty-state title="Belum ada pesanan" description="Pesanan yang Anda buat akan tampil di halaman ini."><x-slot:action><a href="{{ route('catalog') }}" class="btn-primary">Cari produk</a></x-slot:action></x-empty-state>@endif
@endsection
