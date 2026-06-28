@extends('layouts.dashboard')
@section('title', 'Pesanan Sewa')
@section('page-title', 'Pesanan Sewa')
@section('page-subtitle', 'Cari kode booking dan perbarui status pengambilan barang.')
@section('content')
@php
    $statusLabels = [
        'pending' => 'Menunggu Konfirmasi',
        'confirmed' => 'Dikonfirmasi Mitra',
        'ready_pickup' => 'Siap Diambil',
        'ongoing' => 'Sedang Disewa',
        'returned' => 'Dikembalikan',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $paymentLabels = [
        'unpaid' => 'Belum dibayar',
        'waiting_confirmation' => 'Menunggu verifikasi',
        'paid' => 'Lunas',
        'rejected' => 'Ditolak',
        'refunded' => 'Dikembalikan',
    ];
@endphp
<form class="mb-5 grid gap-3 sm:grid-cols-[1fr_240px_auto]">
    <input name="q" value="{{ request('q') }}" class="input" placeholder="Cari kode booking SW-...">
    <select name="status" class="input">
        <option value="">Semua status</option>
        @foreach($statusLabels as $status => $label)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn-primary">Cari</button>
</form>

@if($bookings->count())
    <div class="space-y-4">
        @foreach($bookings as $booking)
            @php $item = $booking->items->first(); @endphp
            <article class="card p-5">
                <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div class="flex gap-4">
                        <img src="{{ $item->product->image_url }}" class="h-24 w-28 rounded-xl object-cover" alt="">
                        <div>
                            <div class="flex flex-wrap items-center gap-2"><p class="text-sm font-black tracking-wide text-indigo-600">{{ $booking->booking_code }}</p><x-status-badge :status="$booking->status" /></div>
                            <h2 class="mt-2 font-black text-ink">{{ $item->product->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $booking->customer_name ?: $booking->customer->name }} • {{ $booking->start_at->translatedFormat('d M') }}–{{ $booking->end_at->translatedFormat('d M Y') }} • {{ $item->quantity }} unit</p>
                            <p class="mt-2 text-xs text-slate-400">Pengambilan di {{ $booking->partner->business_name }} • Status bayar: {{ $paymentLabels[$booking->payment_status] ?? $booking->payment_status }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 lg:max-w-sm lg:justify-end">
                        <p class="mr-3 text-lg font-black text-ink">Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                        <a href="{{ route('mitra.bookings.show', $booking) }}" class="btn-secondary py-2">Detail</a>
                        @if($booking->status === 'pending')
                            <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button class="btn-primary py-2">Konfirmasi</button></form>
                            <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="btn-danger py-2">Batalkan</button></form>
                        @elseif($booking->status === 'confirmed' && $booking->payment_status === 'paid')
                            <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ready_pickup"><button class="btn-primary py-2">Siap Diambil</button></form>
                        @elseif($booking->status === 'ready_pickup')
                            <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ongoing"><button class="btn-primary py-2">Barang Diambil</button></form>
                        @elseif($booking->status === 'ongoing')
                            <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="returned"><button class="btn-primary py-2">Dikembalikan</button></form>
                        @elseif($booking->status === 'returned')
                            <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="btn-primary py-2">Selesaikan</button></form>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <div class="mt-6">{{ $bookings->links() }}</div>
@else
    <x-empty-state title="Pesanan tidak ditemukan" description="Permintaan sewa customer akan tampil di halaman ini." />
@endif
@endsection
