@extends('layouts.dashboard')
@section('title', 'Peninjauan Pembayaran')
@section('page-title', 'Peninjauan Pembayaran')
@section('page-subtitle', 'Cocokkan bukti transfer, rekening pengirim, nominal, dan transaksi penyewaan.')
@section('content')
@php $labels = ['waiting_confirmation' => 'Menunggu verifikasi', 'paid' => 'Lunas', 'rejected' => 'Ditolak', 'pending' => 'Menunggu']; @endphp
<form class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_230px_auto]"><input name="q" value="{{ request('q') }}" class="input" placeholder="Cari kode pembayaran atau booking..."><select name="status" class="input"><option value="">Semua status</option>@foreach($labels as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select><button class="btn-primary">Terapkan</button></form>

@if($payments->count())
    <div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
        @foreach($payments as $payment)
            @php $booking = $payment->booking; @endphp
            <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-100/60">
                <div class="border-b border-dashed border-slate-200 bg-slate-50 p-5">
                    <div class="flex items-center justify-between gap-3"><span class="rounded-lg bg-indigo-100 px-2.5 py-1 text-[11px] font-black tracking-wide text-indigo-700">{{ $payment->payment_code }}</span><x-status-badge :status="$payment->status" /></div>
                    <p class="mt-4 text-xs font-semibold text-slate-400">Nominal transfer</p><p class="mt-1 text-2xl font-black text-ink">Rp{{ number_format($payment->amount, 0, ',', '.') }}</p>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-50 font-black text-indigo-600">{{ str($payment->sender_name ?: $booking->customer_name)->substr(0,1) }}</span><div class="min-w-0"><p class="truncate text-sm font-extrabold text-ink">{{ $payment->sender_name ?: $booking->customer_name }}</p><p class="text-xs text-slate-400">{{ $payment->sender_bank ?: 'Bank belum dicatat' }} · {{ $payment->sender_account ? str($payment->sender_account)->mask('*', 3, -3) : '-' }}</p></div></div>
                    <div class="mt-4 space-y-2 rounded-xl bg-slate-50 p-3 text-xs"><div class="flex justify-between gap-3"><span class="text-slate-400">Booking</span><strong class="text-indigo-600">{{ $booking->booking_code }}</strong></div><div class="flex justify-between gap-3"><span class="text-slate-400">Mitra</span><strong class="truncate text-ink">{{ $booking->partner->business_name }}</strong></div><div class="flex justify-between gap-3"><span class="text-slate-400">Waktu transfer</span><strong class="text-ink">{{ $payment->transfer_at?->translatedFormat('d M Y, H:i') ?: '-' }}</strong></div></div>
                    @if($payment->status === 'rejected' && $payment->rejection_reason)<p class="mt-4 rounded-xl bg-rose-50 p-3 text-xs leading-5 text-rose-700">{{ $payment->rejection_reason }}</p>@endif
                    <a href="{{ route('admin.payments.show', $payment) }}" class="btn-primary mt-5 w-full justify-center">{{ $payment->status === 'waiting_confirmation' ? 'Tinjau pembayaran' : 'Lihat detail' }} →</a>
                </div>
            </article>
        @endforeach
    </div>
    <div class="mt-7">{{ $payments->links() }}</div>
@else
    <x-empty-state title="Pembayaran tidak ditemukan" description="Bukti transfer pelanggan akan tampil di halaman ini." />
@endif
@endsection
