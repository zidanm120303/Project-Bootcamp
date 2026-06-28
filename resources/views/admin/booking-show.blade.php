@extends('layouts.dashboard')
@section('title', 'Transaksi '.$booking->booking_code)
@section('page-title', 'Detail Transaksi')
@section('page-subtitle', $booking->booking_code)
@section('content')
@php $item = $booking->items->first(); @endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div><p class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Kode booking</p><p class="mt-1 text-3xl font-black tracking-wide text-indigo-600">{{ $booking->booking_code }}</p></div>
    <div class="flex gap-2"><a href="{{ route('admin.bookings.index') }}" class="btn-secondary">← Monitoring</a><x-status-badge :status="$booking->status" class="px-4 py-2" /></div>
</div>

<div class="grid items-start gap-6 xl:grid-cols-[1fr_360px]">
    <div class="space-y-6">
        <section class="card p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="font-extrabold text-ink">Data pribadi customer</h2>@if($booking->identity_file)<a href="{{ route('bookings.identity', $booking) }}" class="text-sm font-bold text-indigo-600">Lihat identitas →</a>@endif</div>
            <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Nama lengkap</span><strong class="text-ink">{{ $booking->customer_name }}</strong></p>
                <p><span class="block text-xs text-slate-400">Nomor HP</span>{{ $booking->customer_phone }}</p>
                <p><span class="block text-xs text-slate-400">Email</span>{{ $booking->customer_email }}</p>
                <p><span class="block text-xs text-slate-400">Nomor identitas</span>{{ $booking->identity_number }}</p>
                <p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat</span>{{ $booking->customer_address }}</p>
                @if($booking->customer_notes)<p class="sm:col-span-2"><span class="block text-xs text-slate-400">Catatan tambahan</span>{{ $booking->customer_notes }}</p>@endif
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Detail barang</h2>
            <div class="mt-5 flex flex-col gap-5 sm:flex-row"><img src="{{ $item->product->image_url }}" class="aspect-video w-full rounded-2xl object-cover sm:w-52" alt=""><div class="space-y-3 text-sm"><p class="text-lg font-black text-ink">{{ $item->product->name }}</p><p>{{ $item->quantity }} unit × {{ $item->rental_days }} hari</p><p>{{ $booking->start_at->translatedFormat('d M Y') }} – {{ $booking->end_at->translatedFormat('d M Y') }}</p><p class="font-bold text-ink">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p></div></div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Toko pengambilan</h2>
            <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2"><p><span class="block text-xs text-slate-400">Nama toko</span><strong class="text-ink">{{ $booking->partner->business_name }}</strong></p><p><span class="block text-xs text-slate-400">Kontak</span>{{ $booking->partner->phone }}</p><p class="sm:col-span-2"><span class="block text-xs text-slate-400">Alamat</span>{{ $booking->partner->address }}, {{ $booking->partner->city }}, {{ $booking->partner->province }}</p><p><span class="block text-xs text-slate-400">Jam operasional</span>{{ $booking->partner->operational_hours }}</p><p><span class="block text-xs text-slate-400">Catatan</span>{{ $booking->pickup_note ?: $booking->partner->pickup_note }}</p></div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Audit alur transaksi</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['Dibuat', $booking->created_at],
                    ['Dikonfirmasi mitra', $booking->confirmed_at],
                    ['Siap diambil', $booking->ready_pickup_at],
                    ['Diambil customer', $booking->picked_up_at],
                    ['Dikembalikan', $booking->returned_at],
                    ['Selesai', $booking->completed_at],
                ] as [$label, $time])
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-400">{{ $label }}</p><p class="mt-1 text-sm font-bold {{ $time ? 'text-ink' : 'text-slate-300' }}">{{ $time?->translatedFormat('d M Y, H:i') ?: 'Belum' }}</p></div>
                @endforeach
            </div>
            @if($booking->return_condition)<p class="mt-4 rounded-xl border border-slate-100 p-4 text-sm text-slate-600"><strong>Kondisi pengembalian:</strong><br>{{ $booking->return_condition }}</p>@endif
        </section>
    </div>

    <aside class="card p-6">
        <h2 class="font-extrabold text-ink">Ringkasan transaksi</h2>
        <div class="mt-5 space-y-3 text-sm"><div class="flex justify-between"><span class="text-slate-500">Status sewa</span><x-status-badge :status="$booking->status" /></div><div class="flex justify-between"><span class="text-slate-500">Status bayar</span><x-status-badge :status="$booking->payment_status" /></div><div class="flex justify-between border-t border-slate-100 pt-4"><span class="text-slate-500">Biaya sewa</span><span>Rp{{ number_format($booking->subtotal_amount, 0, ',', '.') }}</span></div><p class="-mt-1 text-[11px] text-slate-400">Rp{{ number_format($item->price_per_unit,0,',','.') }} × {{ $item->rental_days }} hari × {{ $item->quantity }} unit</p><div class="flex justify-between"><span class="text-slate-500">Deposit</span><span>Rp{{ number_format($booking->deposit_amount, 0, ',', '.') }}</span></div><p class="-mt-1 text-[11px] text-slate-400">Rp{{ number_format($item->product->security_deposit,0,',','.') }} × {{ $item->quantity }} unit</p>@if((float)$booking->late_fee > 0)<div class="flex justify-between text-rose-600"><span>Biaya terlambat</span><span>Rp{{ number_format($booking->late_fee,0,',','.') }}</span></div>@endif @if((float)$booking->damage_fee > 0)<div class="flex justify-between text-rose-600"><span>Biaya kerusakan</span><span>Rp{{ number_format($booking->damage_fee,0,',','.') }}</span></div>@endif<div class="flex justify-between border-t border-slate-100 pt-4 text-lg font-black text-ink"><span>Total</span><span>Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</span></div><p class="text-right text-[11px] text-emerald-600">Tanpa biaya layanan</p></div>
        @if((float)$booking->deposit_amount > 0)
            <div class="mt-5 rounded-xl bg-indigo-50 p-4"><div class="flex items-center justify-between text-xs"><span class="font-semibold text-indigo-600">Status deposit</span><strong class="text-indigo-800">{{ match($booking->deposit_status) {'pending' => 'Menunggu pembayaran', 'held' => 'Ditahan', 'pending_refund' => 'Perlu dikembalikan', 'refunded' => 'Sudah dikembalikan', default => 'Tidak berlaku'} }}</strong></div>@if($booking->deposit_refunded_at)<p class="mt-2 text-[11px] text-indigo-500">{{ $booking->deposit_refunded_at->translatedFormat('d M Y, H:i') }}</p>@endif</div>
            @if($booking->deposit_status === 'pending_refund')<form action="{{ route('admin.bookings.deposit', $booking) }}" method="POST" class="mt-3">@csrf @method('PATCH')<button class="btn-primary w-full">Tandai deposit dikembalikan</button></form>@endif
        @endif
    </aside>
</div>
@endsection
