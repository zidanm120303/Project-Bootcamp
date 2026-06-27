@extends('layouts.dashboard')
@section('title','Pengaturan Sistem')
@section('page-title','Pengaturan Sistem')
@section('page-subtitle','Konfigurasi aturan utama platform.')
@section('content')
@php $values=$settings->pluck('value','key'); @endphp
<form action="{{ route('admin.settings.update') }}" method="POST" class="card max-w-3xl p-6">@csrf @method('PATCH')<h2 class="text-lg font-extrabold text-ink">Aturan transaksi & kepercayaan</h2><p class="mt-2 text-sm text-slate-500">Perubahan berlaku untuk booking dan perhitungan skor berikutnya.</p><div class="mt-6 grid gap-5 sm:grid-cols-2"><div><label class="label">Biaya layanan (%)</label><input type="number" name="platform_fee_percent" value="{{ old('platform_fee_percent',$values['platform_fee_percent']??5) }}" class="input" min="0" max="30" step=".1"></div><div><label class="label">Skor minimal Mitra Terpercaya</label><input type="number" name="trusted_min_score" value="{{ old('trusted_min_score',$values['trusted_min_score']??85) }}" class="input" min="0" max="100"></div><div><label class="label">Batas waktu pembayaran (jam)</label><input type="number" name="payment_due_hours" value="{{ old('payment_due_hours',$values['payment_due_hours']??24) }}" class="input" min="1" max="168"></div></div><button class="btn-primary mt-6">Simpan pengaturan</button></form>
@endsection
