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
    <div class="grid gap-5 xl:grid-cols-2">
        @foreach($bookings as $booking)
            @php $item = $booking->items->first(); @endphp
            <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-100/50">
                <div class="h-1.5 {{ match($booking->status) {'completed' => 'bg-emerald-500', 'cancelled','disputed' => 'bg-rose-500', 'ready_pickup','ongoing' => 'bg-blue-500', default => 'bg-indigo-500'} }}"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="min-w-0"><p class="flex items-center gap-2 text-xs font-bold text-slate-500"><x-icon name="store" class="h-4 w-4 text-indigo-500" /><span class="truncate">{{ $booking->partner->business_name }}</span></p><p class="mt-1 text-[11px] text-slate-400">{{ $booking->created_at->translatedFormat('d M Y, H:i') }} · {{ $booking->booking_code }}</p></div>
                        <x-status-badge :status="$booking->status" />
                    </div>

                    <div class="mt-4 flex gap-4">
                        <img src="{{ $item->product->image_url }}" class="h-28 w-32 shrink-0 rounded-2xl object-cover sm:h-32 sm:w-40" alt="{{ $item->product->name }}">
                        <div class="min-w-0 flex-1">
                            <h2 class="line-clamp-2 font-black leading-snug text-ink">{{ $item->product->name }}</h2>
                            <p class="mt-1 text-xs text-slate-400">{{ $item->product->brand }} {{ $item->product->model }} · {{ $item->quantity }} unit</p>
                            <div class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                                <p class="flex items-center gap-2"><x-icon name="calendar" class="h-4 w-4 text-indigo-500" />{{ $booking->start_at->translatedFormat('d M Y') }} - {{ $booking->end_at->translatedFormat('d M Y') }}</p>
                                <p class="mt-2 flex items-start gap-2"><x-icon name="location" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" /><span class="line-clamp-2">{{ $booking->partner->address }}, {{ $booking->partner->city }}</span></p>
                                <p class="mt-2 text-[11px] leading-5 text-slate-500">Kontak {{ $booking->partner->phone }} · {{ $booking->partner->operational_hours ?: 'Konfirmasi jam operasional' }}<br>{{ $booking->pickup_note ?: $booking->partner->pickup_note }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50/60 p-3"><p class="text-xs font-extrabold text-indigo-800">{{ $statusLabels[$booking->status] ?? $booking->status }}</p><p class="mt-1 text-xs leading-5 text-indigo-600">{{ $statusHints[$booking->status] ?? 'Pantau detail transaksi untuk informasi terbaru.' }}</p></div>

                    <div class="mt-4 flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-end sm:justify-between">
                        <div><p class="text-[11px] text-slate-400">Total pembayaran</p><p class="mt-1 text-xl font-black text-ink">Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</p><div class="mt-1"><x-status-badge :status="$booking->payment_status" /></div></div>
                        <div class="flex gap-2"><a href="{{ route('customer.bookings.invoice', $booking) }}" class="btn-secondary py-2">Invoice</a><a href="{{ route('customer.bookings.show', $booking) }}" class="btn-primary py-2">Lihat detail →</a></div>
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
