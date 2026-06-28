@extends('layouts.dashboard')
@section('title', 'Pesanan '.$booking->booking_code)
@section('page-title', 'Detail Pesanan Sewa')

@section('content')
@php $item = $booking->items->first(); @endphp

{{-- Header --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
   
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Kode pengambilan</p>
        <p class="text-2xl font-black tracking-wide text-indigo-600">{{ $booking->booking_code }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('mitra.bookings.index') }}" class="btn-secondary">&larr; Daftar pesanan</a>
        <x-status-badge :status="$booking->status" class="px-4 py-2" />
    </div>
</div>

<div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="space-y-5">

        {{-- Produk --}}
        <section class="card p-4 sm:p-5">
            <div class="flex gap-4">
                <img src="{{ $item->product->image_url }}"
                    alt="{{ $item->product->name }}"
                    class="h-20 w-20 shrink-0 rounded-lg object-cover sm:h-24 sm:w-24">
                <div class="min-w-0 flex-1">
                    <h2 class="font-black leading-snug text-ink">{{ $item->product->name }}</h2>
                    <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-4">
                        <div>
                            <span class="block text-xs text-slate-400">Tanggal sewa</span>
                            <strong class="text-ink">{{ $booking->start_at->translatedFormat('d M Y') }}</strong>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-400">Tanggal kembali</span>
                            <strong class="text-ink">{{ $booking->end_at->translatedFormat('d M Y') }}</strong>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-400">Jumlah</span>
                            <strong class="text-ink">{{ $item->quantity }} unit</strong>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-400">Durasi</span>
                            <strong class="text-ink">{{ $item->rental_days }} hari</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Data Customer --}}
        <section class="card p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-extrabold text-ink">Indentitas Customer</h2>
                @if($booking->identity_file)
                <a href="{{ route('bookings.identity', $booking) }}" class="text-sm font-bold text-indigo-600">Lihat identitas &rarr;</a>
                @endif
            </div>
            <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Nama lengkap</span><strong class="text-ink">{{ $booking->customer_name }}</strong></p>
                <p><span class="block text-xs text-slate-400">Nomor HP / WhatsApp</span><a href="tel:{{ $booking->customer_phone }}" class="font-bold text-indigo-600">{{ $booking->customer_phone }}</a></p>
                <p><span class="block text-xs text-slate-400">Email</span>{{ $booking->customer_email ?: '-' }}</p>
                <p><span class="block text-xs text-slate-400">Nomor identitas</span>{{ $booking->identity_number ?: '-' }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat</span>{{ $booking->customer_address ?: '-' }}</p>
                @if($booking->customer_notes)
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Catatan customer</span>{{ $booking->customer_notes }}</p>
                @endif
            </div>
            @unless($booking->identity_file)
            <p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-700">Dokumen identitas belum tersedia pada transaksi lama ini.</p>
            @endunless
        </section>

        {{-- Lokasi Pengambilan --}}
        <section class="card p-4 sm:p-5">
            <h2 class="font-extrabold text-ink">Lokasi pengambilan</h2>
            <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Toko</span><strong class="text-ink">{{ $booking->partner->business_name }}</strong></p>
                <p><span class="block text-xs text-slate-400">Kontak toko</span>{{ $booking->partner->phone ?: '-' }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat</span>{{ $booking->partner->address }}, {{ $booking->partner->city }}, {{ $booking->partner->province }}</p>
                <p><span class="block text-xs text-slate-400">Jam operasional</span>{{ $booking->partner->operational_hours ?: '-' }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Catatan pengambilan</span>{{ $booking->pickup_note ?: $booking->partner->pickup_note ?: 'Tunjukkan kode booking saat customer mengambil barang.' }}</p>
            </div>
        </section>

        {{-- Jejak Transaksi --}}
        <section class="card p-4 sm:p-5">
            <h2 class="font-extrabold text-ink">Jejak transaksi</h2>
            <div class="mt-4 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                ['Pesanan dibuat', $booking->created_at],
                ['Dikonfirmasi', $booking->confirmed_at],
                ['Siap diambil', $booking->ready_pickup_at],
                ['Kamera diambil', $booking->picked_up_at],
                ['Dikembalikan', $booking->returned_at],
                ['Selesai', $booking->completed_at],
                ] as [$label, $time])
                <div class="rounded-lg border border-slate-100 p-3">
                    <p class="text-xs text-slate-400">{{ $label }}</p>
                    <p class="mt-0.5 font-semibold {{ $time ? 'text-ink' : 'text-slate-300' }}">
                        {{ $time?->translatedFormat('d M Y, H:i') ?: '–' }}
                    </p>
                </div>
                @endforeach
            </div>
        </section>

    </div>

    {{-- Sidebar --}}
    <aside class="space-y-5">

        {{-- Perbarui Status --}}
        <section class="card p-4 sm:p-5">
            <h2 class="font-extrabold text-ink">Perbarui status</h2>
            <p class="mt-1 text-sm text-slate-500">Cocokkan kode booking dan identitas customer sebelum menyerahkan barang.</p>
            <div class="mt-4 space-y-2">
                @if($booking->status === 'pending')
                <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button class="btn-primary w-full">Konfirmasi pesanan</button></form>
                <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="btn-danger w-full">Batalkan pesanan</button></form>
                @elseif($booking->status === 'confirmed')
                @if($booking->payment_status === 'paid')
                <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ready_pickup"><button class="btn-primary w-full">Tandai siap diambil</button></form>
                @else
                <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-700">Menunggu pembayaran customer diverifikasi.</p>
                @endif
                @elseif($booking->status === 'ready_pickup')
                <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ongoing"><button class="btn-primary w-full">Konfirmasi barang diambil</button></form>
                @elseif($booking->status === 'ongoing')
                <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}" class="space-y-2">@csrf @method('PATCH')
                    <input type="hidden" name="status" value="returned">
                    <textarea name="return_condition" class="input text-sm" rows="4" placeholder="Catat kondisi kamera, lensa, baterai, dan aksesori saat kembali..." required></textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" min="0" name="late_fee" class="input px-3 text-sm" placeholder="Biaya terlambat">
                        <input type="number" min="0" name="damage_fee" class="input px-3 text-sm" placeholder="Biaya kerusakan">
                    </div>
                    <button class="btn-primary w-full">Konfirmasi dikembalikan</button>
                </form>
                @elseif($booking->status === 'returned')
                @if($booking->payment_status === 'paid')
                <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="btn-primary w-full">Selesaikan transaksi</button></form>
                @else
                <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-700">Menunggu pelunasan biaya tambahan sebelum transaksi dapat diselesaikan.</p>
                @endif
                @else
                <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">Tidak ada tindakan status yang tersedia.</p>
                @endif
            </div>
        </section>

        {{-- Pembayaran --}}
        <section class="card p-4 sm:p-5">
            <h2 class="font-extrabold text-ink">Pembayaran</h2>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-500">Status</span>
                    <x-status-badge :status="$booking->payment_status" />
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3 text-base font-black text-ink">
                    <span>Total</span>
                    <span>Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            @if($booking->return_condition)
            <div class="mt-3 rounded-lg bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                <strong>Kondisi pengembalian:</strong><br>{{ $booking->return_condition }}
            </div>
            @endif
        </section>

    </aside>
</div>
@endsection