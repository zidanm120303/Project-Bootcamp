@extends('layouts.dashboard')
@section('title', 'Detail '.$booking->booking_code)
@section('page-title', 'Detail Penyewaan')
@section('page-subtitle', $booking->booking_code)
@section('content')
@php
    $steps = [
        'pending' => 'Menunggu Konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'ready_pickup' => 'Siap Diambil',
        'ongoing' => 'Sedang Disewa',
        'returned' => 'Dikembalikan',
        'completed' => 'Selesai',
    ];
    $order = array_keys($steps);
    $stepTimes = [
        'pending' => $booking->created_at,
        'confirmed' => $booking->confirmed_at,
        'ready_pickup' => $booking->ready_pickup_at,
        'ongoing' => $booking->picked_up_at,
        'returned' => $booking->returned_at,
        'completed' => $booking->completed_at,
    ];
    $current = array_search($booking->status, $order, true);
    $current = $current === false ? -1 : $current;
    $item = $booking->items->first();
@endphp

<section class="card mb-6 p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Kode unik penyewaan</p>
            <p class="mt-1 text-2xl font-black tracking-wide text-indigo-600">{{ $booking->booking_code }}</p>
            <p class="mt-2 text-sm text-slate-500">Tunjukkan kode ini bersama identitas asli saat mengambil barang di toko mitra.</p>
        </div>
        <div class="flex gap-2"><a href="{{ route('customer.bookings.invoice', $booking) }}" class="btn-secondary">Cetak invoice</a><x-status-badge :status="$booking->status" class="px-4 py-2" /></div>
    </div>

    @if($booking->status === 'cancelled')
        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">Pesanan ini telah dibatalkan.</div>
    @else
        <div class="mt-8 grid grid-cols-3 gap-y-5 sm:grid-cols-6">
            @foreach($steps as $status => $label)
                @php $index = array_search($status, $order, true); @endphp
                <div class="relative text-center">
                    <span class="relative z-10 mx-auto grid h-9 w-9 place-items-center rounded-full text-sm font-black {{ $index <= $current ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-400' }}">{{ $index + 1 }}</span>
                    <p class="mt-2 text-[11px] font-bold {{ $index <= $current ? 'text-indigo-700' : 'text-slate-400' }}">{{ $label }}</p>
                    @if($stepTimes[$status])<p class="mt-1 text-[9px] text-slate-400">{{ $stepTimes[$status]->translatedFormat('d M, H:i') }}</p>@endif
                    @if(!$loop->last)<span class="absolute left-1/2 top-4 hidden h-0.5 w-full sm:block {{ $index < $current ? 'bg-indigo-500' : 'bg-slate-100' }}"></span>@endif
                </div>
            @endforeach
        </div>
    @endif
</section>

<div class="grid items-start gap-6 xl:grid-cols-[1fr_370px]">
    <div class="space-y-6">
        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Barang dan jadwal sewa</h2>
            <div class="mt-5 flex flex-col gap-5 sm:flex-row">
                <img src="{{ $item->product->image_url }}" class="aspect-video w-full rounded-2xl object-cover sm:w-48" alt="">
                <div class="space-y-3 text-sm">
                    <p class="font-extrabold text-ink">{{ $item->product->name }}</p>
                    <p class="text-slate-500">Tanggal sewa: <span class="font-semibold text-slate-700">{{ $booking->start_at->translatedFormat('d M Y') }}</span></p>
                    <p class="text-slate-500">Tanggal kembali: <span class="font-semibold text-slate-700">{{ $booking->end_at->translatedFormat('d M Y') }}</span></p>
                    <p class="text-slate-500">Jumlah unit: <span class="font-semibold text-slate-700">{{ $item->quantity }} unit</span></p>
                    <p class="text-slate-500">Durasi: <span class="font-semibold text-slate-700">{{ $item->rental_days }} hari</span></p>
                </div>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Lokasi pengambilan dan pengembalian</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="space-y-3 text-sm">
                    <p><span class="block text-xs text-slate-400">Toko mitra</span><strong class="text-ink">{{ $booking->partner->business_name }}</strong></p>
                    <p><span class="block text-xs text-slate-400">Alamat lengkap</span>{{ $booking->partner->address }}, {{ $booking->partner->city }}, {{ $booking->partner->province }} {{ $booking->partner->postal_code }}</p>
                    <p><span class="block text-xs text-slate-400">WhatsApp / kontak</span><a href="tel:{{ $booking->partner->phone }}" class="font-bold text-indigo-600">{{ $booking->partner->phone }}</a></p>
                </div>
                <div class="rounded-2xl bg-indigo-50 p-4 text-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-500">Jam operasional</p>
                    <p class="mt-2 font-extrabold text-ink">{{ $booking->partner->operational_hours ?: 'Konfirmasi kepada mitra.' }}</p>
                    <p class="mt-4 text-xs leading-5 text-slate-500">{{ $booking->pickup_note ?: $booking->partner->pickup_note ?: 'Tunjukkan kode booking saat mengambil barang.' }}</p>
                </div>
            </div>
        </section>

        <section class="card p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="font-extrabold text-ink">Data penyewa</h2>@if($booking->identity_file)<a href="{{ route('bookings.identity', $booking) }}" class="text-sm font-bold text-indigo-600">Lihat identitas →</a>@endif</div>
            <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Nama lengkap</span><strong class="text-ink">{{ $booking->customer_name }}</strong></p>
                <p><span class="block text-xs text-slate-400">Nomor HP</span>{{ $booking->customer_phone }}</p>
                <p><span class="block text-xs text-slate-400">Email</span>{{ $booking->customer_email }}</p>
                <p><span class="block text-xs text-slate-400">Nomor identitas</span>{{ $booking->identity_number }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat</span>{{ $booking->customer_address }}</p>
                @if($booking->customer_notes)<p class="sm:col-span-2"><span class="block text-xs text-slate-400">Catatan</span>{{ $booking->customer_notes }}</p>@endif
            </div>
        </section>

        @if(in_array($booking->status, ['confirmed', 'returned']) && $booking->outstanding_amount > 0 && ! $booking->payments->contains('status', 'waiting_confirmation'))
            <section class="card p-6">
                <h2 class="font-extrabold text-ink">{{ $booking->status === 'returned' ? 'Pelunasan biaya tambahan' : 'Pembayaran penyewaan' }}</h2>
                <p class="mt-2 text-sm text-slate-500">Transfer <strong>Rp{{ number_format($booking->outstanding_amount, 0, ',', '.') }}</strong> ke BCA 1234567890 a.n. PT RentalPro Indonesia.</p>
                <form action="{{ route('customer.payments.store', $booking) }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-3 sm:grid-cols-2">
                    @csrf
                    <div><label class="label">Nama pemilik rekening</label><input name="sender_name" class="input" required></div>
                    <div><label class="label">Bank pengirim</label><input name="sender_bank" class="input" required></div>
                    <div><label class="label">Nomor rekening pengirim</label><input name="sender_account" class="input" required></div>
                    <div><label class="label">Waktu transfer</label><input type="datetime-local" name="transfer_at" class="input" required></div>
                    <div class="sm:col-span-2"><label class="label">Bukti transfer</label><input type="file" name="proof_file" class="input" accept=".jpg,.jpeg,.png,.pdf" required></div>
                    <button class="btn-primary whitespace-nowrap sm:col-span-2">Kirim bukti pembayaran</button>
                </form>
            </section>
        @elseif($booking->payment_status === 'waiting_confirmation')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800"><p class="font-extrabold">Bukti pembayaran sedang diverifikasi.</p><p class="mt-1">Status pembayaran akan berubah setelah admin melakukan validasi.</p></div>
        @endif

        @if($booking->status === 'completed' && $booking->reviews->isEmpty())
            <section class="card p-6">
                <h2 class="font-extrabold text-ink">Bagikan pengalaman Anda</h2>
                <form action="{{ route('customer.reviews.store', $booking) }}" method="POST" class="mt-5 space-y-4">@csrf
                    <select name="rating" class="input"><option value="5">★★★★★ — Sangat puas</option><option value="4">★★★★ — Puas</option><option value="3">★★★ — Cukup</option><option value="2">★★ — Kurang</option><option value="1">★ — Buruk</option></select>
                    <textarea name="review_text" class="input" rows="3" placeholder="Ceritakan pengalaman Anda..."></textarea>
                    <button class="btn-primary">Kirim ulasan</button>
                </form>
            </section>
        @endif
    </div>

    <aside class="card p-6">
        <h2 class="font-extrabold text-ink">Ringkasan pembayaran</h2>
        <div class="mt-5 space-y-3 text-sm">
            <div class="rounded-xl bg-slate-50 p-3">
                <div class="flex justify-between gap-3 text-slate-700"><span class="font-semibold">Biaya sewa</span><span class="font-bold">Rp{{ number_format($booking->subtotal_amount, 0, ',', '.') }}</span></div>
                <p class="mt-1 text-[11px] leading-5 text-slate-500">Rp{{ number_format($item->price_per_unit, 0, ',', '.') }} × {{ $item->rental_days }} hari × {{ $item->quantity }} unit</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <div class="flex justify-between gap-3 text-slate-700"><span class="font-semibold">Deposit keamanan</span><span class="font-bold">Rp{{ number_format($booking->deposit_amount, 0, ',', '.') }}</span></div>
                <p class="mt-1 text-[11px] leading-5 text-slate-500">Rp{{ number_format($item->product->security_deposit, 0, ',', '.') }} × {{ $item->quantity }} unit</p>
            </div>
            @if((float)$booking->late_fee > 0)<div class="flex justify-between text-rose-600"><span>Biaya keterlambatan</span><span>Rp{{ number_format($booking->late_fee, 0, ',', '.') }}</span></div>@endif
            @if((float)$booking->damage_fee > 0)<div class="flex justify-between text-rose-600"><span>Biaya kerusakan</span><span>Rp{{ number_format($booking->damage_fee, 0, ',', '.') }}</span></div>@endif
            <div class="flex justify-between border-t border-slate-100 pt-4 text-lg font-black text-ink"><span>Total</span><span>Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</span></div>
        </div>
        <p class="mt-3 text-xs leading-5 text-emerald-600">Tanpa biaya layanan. Deposit dikembalikan sesuai hasil pemeriksaan barang.</p>
        <div class="mt-5 flex items-center justify-between rounded-xl bg-slate-50 p-3"><span class="text-xs font-semibold text-slate-500">Status bayar</span><x-status-badge :status="$booking->payment_status" /></div>
        @if((float)$booking->deposit_amount > 0)<div class="mt-3 flex items-center justify-between rounded-xl bg-indigo-50 p-3"><span class="text-xs font-semibold text-indigo-600">Status deposit</span><span class="text-xs font-bold text-indigo-700">{{ match($booking->deposit_status) {'pending' => 'Menunggu pembayaran', 'held' => 'Ditahan', 'pending_refund' => 'Menunggu pengembalian', 'refunded' => 'Sudah dikembalikan', default => 'Tidak berlaku'} }}</span></div>@endif
        @if(in_array($booking->status, ['pending', 'confirmed']) && in_array($booking->payment_status, ['unpaid', 'rejected']))
            <form action="{{ route('customer.bookings.cancel', $booking) }}" method="POST" class="mt-5">@csrf @method('PATCH')<button class="btn-danger w-full" onclick="return confirm('Batalkan booking ini?')">Batalkan pesanan</button></form>
        @endif
    </aside>
</div>
@endsection
