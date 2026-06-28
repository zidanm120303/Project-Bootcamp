@extends('layouts.dashboard')
@section('title', 'Pembayaran '.$payment->payment_code)
@section('page-title', 'Detail Peninjauan Pembayaran')
@section('page-subtitle', $payment->payment_code)
@section('content')
@php $booking = $payment->booking; $item = $booking->items->first(); @endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div><a href="{{ route('admin.payments.index') }}" class="text-sm font-bold text-indigo-600">← Daftar pembayaran</a><div class="mt-2 flex items-center gap-3"><h2 class="text-2xl font-black text-ink">{{ $payment->payment_code }}</h2><x-status-badge :status="$payment->status" /></div></div>
    <p class="text-2xl font-black text-ink">Rp{{ number_format($payment->amount, 0, ',', '.') }}</p>
</div>

<div class="grid items-start gap-6 xl:grid-cols-[1fr_380px]">
    <div class="space-y-6">
        <section class="card p-6">
            <div class="flex items-center justify-between gap-3"><h3 class="font-extrabold text-ink">Data transfer</h3>@if($payment->proof_file)<a href="{{ route('payments.proof', $payment) }}" class="btn-secondary py-2">Buka bukti transfer</a>@endif</div>
            <div class="mt-5 grid gap-5 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-slate-400">Nama pengirim</span><strong class="text-ink">{{ $payment->sender_name ?: '-' }}</strong></p>
                <p><span class="block text-xs text-slate-400">Bank pengirim</span>{{ $payment->sender_bank ?: '-' }}</p>
                <p><span class="block text-xs text-slate-400">Nomor rekening pengirim</span>{{ $payment->sender_account ?: '-' }}</p>
                <p><span class="block text-xs text-slate-400">Waktu transfer</span>{{ $payment->transfer_at?->translatedFormat('d F Y, H:i') ?: '-' }}</p>
                <p><span class="block text-xs text-slate-400">Metode</span>Transfer bank</p>
                <p><span class="block text-xs text-slate-400">Nominal</span><strong class="text-ink">Rp{{ number_format($payment->amount, 0, ',', '.') }}</strong></p>
            </div>
        </section>

        <section class="card p-6">
            <h3 class="font-extrabold text-ink">Transaksi terkait</h3>
            <div class="mt-5 flex flex-col gap-5 sm:flex-row">
                <img src="{{ $item->product->image_url }}" class="aspect-video w-full rounded-2xl object-cover sm:w-52" alt="">
                <div class="space-y-2 text-sm">
                    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-lg font-black text-indigo-600">{{ $booking->booking_code }}</a>
                    <p class="font-bold text-ink">{{ $item->product->name }}</p>
                    <p class="text-slate-500">{{ $booking->start_at->translatedFormat('d M Y') }} - {{ $booking->end_at->translatedFormat('d M Y') }} · {{ $item->quantity }} unit</p>
                    <p class="text-slate-500">Pelanggan: {{ $booking->customer_name }} · {{ $booking->customer_phone }}</p>
                    <p class="text-slate-500">Mitra: {{ $booking->partner->business_name }}</p>
                </div>
            </div>
        </section>

        @if($payment->status !== 'waiting_confirmation')
            <section class="card p-6"><h3 class="font-extrabold text-ink">Hasil peninjauan</h3><div class="mt-4 grid gap-4 text-sm sm:grid-cols-2"><p><span class="block text-xs text-slate-400">Ditinjau oleh</span>{{ $payment->verifier?->name ?: '-' }}</p><p><span class="block text-xs text-slate-400">Waktu peninjauan</span>{{ $payment->verified_at?->translatedFormat('d F Y, H:i') ?: '-' }}</p><p class="sm:col-span-2"><span class="block text-xs text-slate-400">Catatan</span>{{ $payment->rejection_reason ?: $payment->notes ?: 'Tidak ada catatan.' }}</p></div></section>
        @endif
    </div>

    <aside class="space-y-6 xl:sticky xl:top-24">
        <section class="card p-6">
            <h3 class="font-extrabold text-ink">Pencocokan nominal</h3>
            <div class="mt-5 space-y-3 text-sm"><div class="flex justify-between"><span class="text-slate-500">Tagihan transaksi</span><span>Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</span></div><div class="flex justify-between"><span class="text-slate-500">Sudah terbayar</span><span>Rp{{ number_format($booking->paid_amount, 0, ',', '.') }}</span></div><div class="flex justify-between border-t border-slate-100 pt-3 font-black text-ink"><span>Bukti ini</span><span>Rp{{ number_format($payment->amount, 0, ',', '.') }}</span></div></div>
        </section>
        @if($payment->status === 'waiting_confirmation')
            <section class="card p-6">
                <h3 class="font-extrabold text-ink">Keputusan pembayaran</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">Pastikan nama, rekening, nominal, tanggal, dan bukti transfer saling cocok.</p>
                <form action="{{ route('admin.payments.update', $payment) }}" method="POST" class="mt-5 space-y-3">
                    @csrf @method('PATCH')
                    <textarea name="notes" class="input" rows="3" placeholder="Catatan internal (opsional)"></textarea>
                    <textarea name="rejection_reason" class="input" rows="3" placeholder="Alasan wajib jika pembayaran ditolak"></textarea>
                    <button name="status" value="paid" class="btn-primary w-full">Terima pembayaran</button>
                    <button name="status" value="rejected" class="btn-danger w-full">Tolak pembayaran</button>
                </form>
            </section>
        @endif
    </aside>
</div>
@endsection
