@extends('layouts.dashboard')
@section('title','Detail '.$booking->booking_code)
@section('page-title','Detail Pesanan')
@section('page-subtitle',$booking->booking_code)
@section('content')
@php
$steps=['pending'=>'Diajukan','waiting_payment'=>'Dibayar','paid'=>'Dibayar','prepared'=>'Diproses','ongoing'=>'Berjalan','completed'=>'Selesai'];
$order=array_keys($steps); $current=array_search($booking->status,$order); if($current===false)$current=-1;
@endphp
<div class="card mb-6 p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm font-bold text-indigo-600">{{ $booking->booking_code }}</p><h1 class="mt-1 text-2xl font-black text-ink">{{ $booking->items->first()->product->name }}</h1></div><x-status-badge :status="$booking->status" class="px-4 py-2" /></div>
    <div class="mt-8 grid grid-cols-3 gap-y-5 sm:grid-cols-6">
        @foreach($steps as $status=>$label) @php $i=array_search($status,$order); @endphp
        <div class="relative text-center"><span class="relative z-10 mx-auto grid h-9 w-9 place-items-center rounded-full text-sm font-black {{ $i <= $current ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-400' }}">{{ $i+1 }}</span><p class="mt-2 text-[11px] font-bold {{ $i <= $current ? 'text-indigo-700' : 'text-slate-400' }}">{{ $label }}</p>@if(!$loop->last)<span class="absolute left-1/2 top-4 hidden h-0.5 w-full sm:block {{ $i < $current ? 'bg-indigo-500' : 'bg-slate-100' }}"></span>@endif</div>
        @endforeach
    </div>
</div>
<div class="grid items-start gap-6 xl:grid-cols-[1fr_360px]">
    <div class="space-y-6">
        <section class="card p-6"><h2 class="font-extrabold text-ink">Detail produk & jadwal</h2><div class="mt-5 flex flex-col gap-5 sm:flex-row"><img src="{{ $booking->items->first()->product->image_url }}" class="aspect-video w-full rounded-2xl object-cover sm:w-48"><div class="space-y-3 text-sm"><p class="font-extrabold text-ink">{{ $booking->items->first()->product->name }}</p><p class="text-slate-500">Mitra: <span class="font-semibold text-slate-700">{{ $booking->partner->business_name }}</span></p><p class="text-slate-500">Jadwal: <span class="font-semibold text-slate-700">{{ $booking->start_at->translatedFormat('d M Y') }} – {{ $booking->end_at->translatedFormat('d M Y') }}</span></p><p class="text-slate-500">Jumlah: <span class="font-semibold text-slate-700">{{ $booking->items->first()->qty }} unit</span></p></div></div></section>
        @if(in_array($booking->status,['confirmed','waiting_payment']) && $booking->payment_status !== 'waiting_confirmation')
        <section class="card p-6"><h2 class="font-extrabold text-ink">Upload bukti pembayaran</h2><p class="mt-2 text-sm text-slate-500">Transfer ke BCA 1234567890 a.n. PT Rentra Indonesia, lalu unggah bukti di sini.</p><form action="{{ route('customer.payments.store',$booking) }}" method="POST" enctype="multipart/form-data" class="mt-5 flex flex-col gap-3 sm:flex-row">@csrf<input type="file" name="proof_file" class="input" accept=".jpg,.jpeg,.png,.pdf" required><button class="btn-primary whitespace-nowrap">Kirim bukti</button></form></section>
        @elseif($booking->payment_status==='waiting_confirmation')<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800"><p class="font-extrabold">Bukti pembayaran sedang diverifikasi.</p><p class="mt-1">Anda akan melihat perubahan status setelah admin menyetujui pembayaran.</p></div>@endif
        @if($booking->status==='completed' && $booking->reviews->isEmpty())
        <section class="card p-6"><h2 class="font-extrabold text-ink">Bagikan pengalaman Anda</h2><form action="{{ route('customer.reviews.store',$booking) }}" method="POST" class="mt-5 space-y-4">@csrf<div><label class="label">Rating</label><select name="rating" class="input"><option value="5">★★★★★ — Sangat puas</option><option value="4">★★★★ — Puas</option><option value="3">★★★ — Cukup</option><option value="2">★★ — Kurang</option><option value="1">★ — Buruk</option></select></div><div><label class="label">Ulasan</label><textarea name="review_text" class="input" rows="3" placeholder="Ceritakan pengalaman Anda..."></textarea></div><button class="btn-primary">Kirim ulasan</button></form></section>
        @endif
        @if(!in_array($booking->status,['cancelled','rejected']) && $booking->disputes->isEmpty())
        <details class="card p-6"><summary class="cursor-pointer font-extrabold text-rose-600">Ada masalah? Ajukan komplain</summary><form action="{{ route('customer.disputes.store',$booking) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">@csrf<select name="issue_type" class="input"><option>Keterlambatan</option><option>Kualitas Produk</option><option>Produk tidak sesuai</option><option>Lainnya</option></select><textarea name="description" class="input" rows="4" placeholder="Jelaskan masalah minimal 20 karakter..." required></textarea><input type="file" name="evidence_file" class="input"><button class="btn-danger">Kirim komplain</button></form></details>
        @endif
    </div>
    <aside class="card p-6"><h2 class="font-extrabold text-ink">Ringkasan pembayaran</h2><div class="mt-5 space-y-3 text-sm"><div class="flex justify-between text-slate-500"><span>Subtotal</span><span>Rp{{ number_format($booking->subtotal_amount,0,',','.') }}</span></div><div class="flex justify-between text-slate-500"><span>Biaya layanan</span><span>Rp{{ number_format($booking->platform_fee,0,',','.') }}</span></div><div class="flex justify-between border-t border-slate-100 pt-4 text-lg font-black text-ink"><span>Total</span><span>Rp{{ number_format($booking->total_amount,0,',','.') }}</span></div></div><div class="mt-5 flex items-center justify-between rounded-xl bg-slate-50 p-3"><span class="text-xs font-semibold text-slate-500">Status bayar</span><x-status-badge :status="$booking->payment_status" /></div>@if(in_array($booking->status,['pending','confirmed','waiting_payment']))<form action="{{ route('customer.bookings.cancel',$booking) }}" method="POST" class="mt-5">@csrf @method('PATCH')<button class="btn-danger w-full" onclick="return confirm('Batalkan booking ini?')">Batalkan pesanan</button></form>@endif</aside>
</div>
@endsection
