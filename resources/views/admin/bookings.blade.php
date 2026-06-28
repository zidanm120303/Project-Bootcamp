@extends('layouts.dashboard')
@section('title', 'Monitoring Transaksi')
@section('page-title', 'Monitoring Transaksi')
@section('page-subtitle', 'Cari kode booking dan validasi transaksi seluruh mitra.')
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
        'disputed' => 'Bermasalah',
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

<div class="table-shell">
    <table class="data-table">
        <thead><tr><th>Booking</th><th>Customer</th><th>Mitra / Lokasi</th><th>Jadwal</th><th>Unit</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($bookings as $booking)
                @php $item = $booking->items->first(); @endphp
                <tr>
                    <td><p class="font-black tracking-wide text-indigo-600">{{ $booking->booking_code }}</p><p class="mt-1 max-w-[180px] truncate text-xs text-slate-400">{{ $item->product->name }}</p></td>
                    <td><p class="font-bold text-ink">{{ $booking->customer_name ?: $booking->customer->name }}</p><p class="text-xs text-slate-400">{{ $booking->customer_phone ?: $booking->customer->phone }}</p></td>
                    <td><p class="font-semibold text-ink">{{ $booking->partner->business_name }}</p><p class="max-w-[180px] truncate text-xs text-slate-400">{{ $booking->partner->city }}</p></td>
                    <td class="whitespace-nowrap">{{ $booking->start_at?->translatedFormat('d M') }}–{{ $booking->end_at?->translatedFormat('d M Y') }}</td>
                    <td class="text-center font-bold">{{ $item->quantity }}</td>
                    <td class="font-extrabold text-ink">Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                    <td><x-status-badge :status="$booking->payment_status" /></td>
                    <td><x-status-badge :status="$booking->status" /></td>
                    <td><a href="{{ route('admin.bookings.show', $booking) }}" class="btn-secondary py-2">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="py-12 text-center text-slate-400">Transaksi tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $bookings->links() }}</div>
@endsection
