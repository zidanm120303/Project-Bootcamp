@extends('layouts.dashboard')
@section('title','Dashboard Customer')
@section('page-title','Halo, '.str(auth()->user()->name)->before(' ').'!')
@section('page-subtitle','Pantau pesanan dan temukan kebutuhan berikutnya.')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card label="Pesanan aktif" :value="$stats['active']" icon="calendar" />
    <x-stat-card label="Menunggu pembayaran" :value="$stats['waitingPayment']" icon="card" tone="amber" />
    <x-stat-card label="Pesanan selesai" :value="$stats['completed']" icon="shield" tone="emerald" />
    <x-stat-card label="Ulasan diberikan" :value="$stats['reviews']" icon="star" tone="violet" />
</div>
<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_340px]">
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5"><div><h2 class="font-extrabold text-ink">Pesanan terbaru</h2><p class="mt-1 text-xs text-slate-500">Status pemesanan Anda saat ini</p></div><a href="{{ route('customer.bookings.index') }}" class="text-sm font-bold text-indigo-600">Lihat semua →</a></div>
        @forelse($recentBookings as $booking)
            <a href="{{ route('customer.bookings.show',$booking) }}" class="flex flex-col gap-3 border-b border-slate-100 p-5 transition last:border-0 hover:bg-slate-50 sm:flex-row sm:items-center">
                <img src="{{ $booking->items->first()->product->image_url }}" class="h-14 w-16 rounded-xl object-cover"><div class="min-w-0 flex-1"><p class="truncate text-sm font-extrabold text-ink">{{ $booking->items->first()->product->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $booking->booking_code }} • {{ $booking->partner->business_name }}</p></div><div class="flex items-center justify-between gap-5 sm:justify-end"><x-status-badge :status="$booking->status" /><p class="text-sm font-extrabold text-ink">Rp{{ number_format($booking->total_amount,0,',','.') }}</p></div>
            </a>
        @empty
            <div class="p-6"><x-empty-state title="Belum ada pesanan" description="Jelajahi katalog untuk menemukan barang atau jasa yang Anda butuhkan."><x-slot:action><a href="{{ route('catalog') }}" class="btn-primary">Jelajahi katalog</a></x-slot:action></x-empty-state></div>
        @endforelse
    </section>
    <aside class="space-y-5">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-700 p-6 text-white shadow-xl shadow-indigo-200"><span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/15"><x-icon name="search" /></span><h2 class="mt-5 text-xl font-black">Ada rencana baru?</h2><p class="mt-2 text-sm leading-6 text-indigo-100">Temukan perlengkapan dan tenaga profesional dari UMKM terverifikasi.</p><a href="{{ route('catalog') }}" class="mt-5 inline-flex rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-indigo-700">Cari kebutuhan →</a></div>
        <div class="card p-5"><h3 class="font-extrabold text-ink">Kenapa aman?</h3><div class="mt-4 space-y-4">@foreach([['calendar','Jadwal tervalidasi'],['box','Stok diperiksa otomatis'],['shield','Mitra terverifikasi']] as [$icon,$text])<p class="flex items-center gap-3 text-sm font-semibold text-slate-600"><span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><x-icon :name="$icon" class="h-4 w-4" /></span>{{ $text }}</p>@endforeach</div></div>
    </aside>
</div>
@endsection
