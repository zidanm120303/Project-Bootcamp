@extends('layouts.dashboard')
@section('title', 'Pesanan '.$booking->booking_code)
@section('page-title', 'Detail Pesanan Sewa')
@section('page-subtitle', $booking->booking_code)
@section('content')
@php $item = $booking->items->first(); @endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div><p class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Kode verifikasi pengambilan</p><p class="mt-1 text-3xl font-black tracking-wide text-indigo-600">{{ $booking->booking_code }}</p></div>
    <div class="flex items-center gap-2"><a href="{{ route('mitra.bookings.index') }}" class="btn-secondary">← Daftar pesanan</a><x-status-badge :status="$booking->status" class="px-4 py-2" /></div>
</div>

<div class="grid items-start gap-6 xl:grid-cols-[1fr_360px]">
    <div class="space-y-6">
        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Validasi customer</h2>
            <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Nama lengkap</span><strong class="text-ink">{{ $booking->customer_name }}</strong></p>
                <p><span class="block text-xs text-slate-400">Nomor HP / WhatsApp</span><a href="tel:{{ $booking->customer_phone }}" class="font-bold text-indigo-600">{{ $booking->customer_phone }}</a></p>
                <p><span class="block text-xs text-slate-400">Email</span>{{ $booking->customer_email }}</p>
                <p><span class="block text-xs text-slate-400">Nomor identitas</span>{{ $booking->identity_number }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat lengkap</span>{{ $booking->customer_address }}</p>
                @if($booking->customer_notes)<p class="sm:col-span-2"><span class="block text-xs text-slate-400">Catatan customer</span>{{ $booking->customer_notes }}</p>@endif
            </div>
            @if($booking->identity_file)
                <a href="{{ route('bookings.identity', $booking) }}" class="btn-secondary mt-5">Buka dokumen identitas</a>
            @else
                <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-700">Dokumen identitas belum tersedia pada transaksi lama ini.</p>
            @endif
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Barang yang disewa</h2>
            <div class="mt-5 flex flex-col gap-5 sm:flex-row">
                <img src="{{ $item->product->image_url }}" class="aspect-video w-full rounded-2xl object-cover sm:w-52" alt="">
                <div class="space-y-3 text-sm">
                    <p class="text-lg font-black text-ink">{{ $item->product->name }}</p>
                    <p class="text-slate-500">Tanggal sewa: <strong class="text-slate-700">{{ $booking->start_at->translatedFormat('d M Y') }}</strong></p>
                    <p class="text-slate-500">Tanggal kembali: <strong class="text-slate-700">{{ $booking->end_at->translatedFormat('d M Y') }}</strong></p>
                    <p class="text-slate-500">Jumlah: <strong class="text-slate-700">{{ $item->quantity }} unit</strong></p>
                    <p class="text-slate-500">Durasi: <strong class="text-slate-700">{{ $item->rental_days }} hari</strong></p>
                </div>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Lokasi pengambilan</h2>
            <div class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Toko</span><strong class="text-ink">{{ $booking->partner->business_name }}</strong></p>
                <p><span class="block text-xs text-slate-400">Kontak toko</span>{{ $booking->partner->phone }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat</span>{{ $booking->partner->address }}, {{ $booking->partner->city }}, {{ $booking->partner->province }}</p>
                <p><span class="block text-xs text-slate-400">Jam operasional</span>{{ $booking->partner->operational_hours }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Catatan pengambilan</span>{{ $booking->pickup_note ?: $booking->partner->pickup_note }}</p>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Jejak transaksi</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['Pesanan dibuat', $booking->created_at],
                    ['Dikonfirmasi', $booking->confirmed_at],
                    ['Siap diambil', $booking->ready_pickup_at],
                    ['Kamera diambil', $booking->picked_up_at],
                    ['Dikembalikan', $booking->returned_at],
                    ['Selesai', $booking->completed_at],
                ] as [$label, $time])
                    <div class="rounded-xl border border-slate-100 p-3"><p class="text-xs text-slate-400">{{ $label }}</p><p class="mt-1 text-sm font-bold {{ $time ? 'text-ink' : 'text-slate-300' }}">{{ $time?->translatedFormat('d M Y, H:i') ?: 'Belum' }}</p></div>
                @endforeach
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Perbarui status</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Pastikan kode booking dan identitas customer cocok sebelum menyerahkan barang.</p>
            <div class="mt-5 space-y-3">
                @if($booking->status === 'pending')
                    <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button class="btn-primary w-full">Konfirmasi pesanan</button></form>
                    <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="btn-danger w-full">Batalkan pesanan</button></form>
                @elseif($booking->status === 'confirmed')
                    @if($booking->payment_status === 'paid')
                        <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ready_pickup"><button class="btn-primary w-full">Tandai siap diambil</button></form>
                    @else
                        <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-700">Menunggu pembayaran customer diverifikasi.</p>
                    @endif
                @elseif($booking->status === 'ready_pickup')
                    <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ongoing"><button class="btn-primary w-full">Konfirmasi barang diambil</button></form>
                @elseif($booking->status === 'ongoing')
                    <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}" class="space-y-3">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="returned">
                        <textarea name="return_condition" class="input text-sm" rows="4" placeholder="Catat kondisi kamera, lensa, baterai, dan aksesori saat kembali..." required></textarea>
                        <div class="grid grid-cols-2 gap-2"><input type="number" min="0" name="late_fee" class="input px-3 text-sm" placeholder="Biaya terlambat"><input type="number" min="0" name="damage_fee" class="input px-3 text-sm" placeholder="Biaya kerusakan"></div>
                        <button class="btn-primary w-full">Konfirmasi dikembalikan</button>
                    </form>
                @elseif($booking->status === 'returned')
                    @if($booking->payment_status === 'paid')
                        <form method="POST" action="{{ route('mitra.bookings.update', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="btn-primary w-full">Selesaikan transaksi</button></form>
                    @else
                        <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-700">Menunggu pelunasan biaya tambahan sebelum transaksi dapat diselesaikan.</p>
                    @endif
                @else
                    <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Tidak ada tindakan status yang tersedia.</p>
                @endif
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Pembayaran</h2>
            <div class="mt-4 flex items-center justify-between"><span class="text-sm text-slate-500">Status</span><x-status-badge :status="$booking->payment_status" /></div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-lg font-black text-ink"><span>Total</span><span>Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</span></div>
            @if($booking->return_condition)<div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-600"><strong>Kondisi pengembalian:</strong><br>{{ $booking->return_condition }}</div>@endif
        </section>
    </aside>
</div>
@endsection
