@extends('layouts.dashboard')
@section('title', 'Pesanan Saya')
@section('page-title', 'Pesanan Saya')
@section('page-subtitle', 'Pantau pembayaran, jadwal pengambilan, masa sewa, dan pengembalian kamera.')
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
    $statusHints = [
        'pending' => 'Mitra sedang memeriksa ketersediaan pesanan Anda.',
        'confirmed' => 'Pesanan dikonfirmasi. Selesaikan pembayaran agar kamera dapat disiapkan.',
        'ready_pickup' => 'Kamera sudah siap. Datang ke toko sesuai jadwal dan tunjukkan kode booking.',
        'ongoing' => 'Kamera sedang Anda gunakan. Jaga unit dan kembalikan tepat waktu.',
        'returned' => 'Kamera sudah kembali dan sedang diperiksa oleh mitra.',
        'completed' => 'Transaksi selesai. Terima kasih telah menggunakan RentalPro.',
        'cancelled' => 'Pesanan ini dibatalkan.',
        'disputed' => 'Pesanan sedang dalam penanganan admin.',
    ];
@endphp

<div class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
    <div><p class="text-sm font-extrabold text-ink">{{ $bookings->total() }} transaksi</p><p class="mt-1 text-xs text-slate-500">Semua pesanan kamera Anda tersimpan di sini.</p></div>
    <form class="flex gap-2"><select name="status" class="input min-w-[220px]" onchange="this.form.submit()"><option value="">Semua status</option>@foreach($statusLabels as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select>@if(request('status'))<a href="{{ route('customer.bookings.index') }}" class="btn-secondary py-2">Reset</a>@endif</form>
</div>

@if($bookings->count())
     <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($bookings as $booking)
    @php $item = $booking->items->first(); @endphp
    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-100/50">

        {{-- Gambar produk --}}
        <div class="relative overflow-hidden">
            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="aspect-[4/3] w-full object-cover transition group-hover:scale-105">
            <div class="absolute left-3 top-3"><x-status-badge :status="$booking->status" /></div>
            <div class="absolute right-3 top-3 rounded-lg bg-white/90 px-2 py-1 text-[10px] font-bold text-slate-600 backdrop-blur">{{ $booking->booking_code }}</div>
        </div>

        {{-- Deskripsi status di bawah foto --}}
        <div class="border-b border-indigo-100 bg-indigo-50 px-4 py-2.5">
            <p class="text-[11px] font-bold text-indigo-700">{{ $statusLabels[$booking->status] ?? $booking->status }}</p>
            <p class="mt-0.5 line-clamp-2 text-[11px] leading-5 text-indigo-500">{{ $statusHints[$booking->status] ?? '-' }}</p>
        </div>

        {{-- Konten card --}}
        <div class="p-4">
            {{-- Nama mitra --}}
            <p class="flex items-center gap-1.5 text-xs font-bold text-indigo-600">
                <x-icon name="store" class="h-4 w-4 shrink-0" />
                {{ $booking->partner->business_name }}
            </p>

            {{-- Nama produk --}}
            <h2 class="mt-1.5 line-clamp-2 font-black leading-snug text-ink">{{ $item->product->name }}</h2>

            {{-- Tanggal & lokasi --}}
            <div class="mt-3 space-y-1.5 text-xs text-slate-500">
                <p class="flex items-center gap-1.5">
                    <x-icon name="calendar" class="h-4 w-4 shrink-0 text-indigo-500" />
                    {{ $booking->start_at->translatedFormat('d M Y') }} → {{ $booking->end_at->translatedFormat('d M Y') }}
                </p>
                <p class="flex items-center gap-1.5">
                    <x-icon name="location" class="h-4 w-4 shrink-0 text-indigo-500" />
                    {{ $booking->partner->city }}
                </p>
                <p class="flex items-start gap-1.5">
                    <x-icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                    <span>{{ $booking->partner->operational_hours ?: 'Konfirmasi jam operasional kepada mitra.' }}</span>
                </p>
                <p class="line-clamp-2 pl-5 text-[11px] leading-5 text-slate-400">{{ $booking->pickup_note ?: $booking->partner->pickup_note }}</p>
            </div>

            {{-- Total & tombol --}}
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                <div>
                    <p class="text-[10px] text-slate-400">Total</p>
                    <p class="text-base font-black text-ink">Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                    <div class="mt-1"><x-status-badge :status="$booking->payment_status" /></div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('customer.bookings.invoice', $booking) }}" class="btn-secondary py-1.5 text-xs">Invoice</a>
                    <a href="{{ route('customer.bookings.show', $booking) }}" class="btn-primary py-1.5 text-xs">Detail →</a>
                </div>
            </div>
        </div>
    </article>
@endforeach
    </div>
    <div class="mt-7">{{ $bookings->links() }}</div>
@else
    <x-empty-state title="Belum ada pesanan" description="Temukan kamera dan perlengkapan produksi untuk kebutuhan Anda."><x-slot:action><a href="{{ route('catalog') }}" class="btn-primary">Jelajahi katalog</a></x-slot:action></x-empty-state>
@endif
@endsection
