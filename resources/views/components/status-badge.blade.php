@props(['status'])
@php
$map = [
    'active' => ['Aktif', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
    'verified' => ['Terverifikasi', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
    'paid' => ['Lunas', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
    'completed' => ['Selesai', 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
    'ongoing' => ['Berjalan', 'bg-sky-50 text-sky-700 ring-sky-200'],
    'prepared' => ['Disiapkan', 'bg-blue-50 text-blue-700 ring-blue-200'],
    'waiting_payment' => ['Menunggu bayar', 'bg-amber-50 text-amber-700 ring-amber-200'],
    'waiting_confirmation' => ['Verifikasi bayar', 'bg-amber-50 text-amber-700 ring-amber-200'],
    'pending' => ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-200'],
    'pending_review' => ['Ditinjau', 'bg-amber-50 text-amber-700 ring-amber-200'],
    'unpaid' => ['Belum dibayar', 'bg-slate-100 text-slate-600 ring-slate-200'],
    'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-200'],
    'cancelled' => ['Dibatalkan', 'bg-rose-50 text-rose-700 ring-rose-200'],
    'disputed' => ['Bermasalah', 'bg-rose-50 text-rose-700 ring-rose-200'],
    'suspended' => ['Ditangguhkan', 'bg-rose-50 text-rose-700 ring-rose-200'],
    'inactive' => ['Nonaktif', 'bg-slate-100 text-slate-600 ring-slate-200'],
    'draft' => ['Draf', 'bg-slate-100 text-slate-600 ring-slate-200'],
    'open' => ['Terbuka', 'bg-rose-50 text-rose-700 ring-rose-200'],
    'reviewed' => ['Ditinjau', 'bg-sky-50 text-sky-700 ring-sky-200'],
    'resolved' => ['Terselesaikan', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
];
[$label, $classes] = $map[$status] ?? [str($status)->replace('_', ' ')->title(), 'bg-slate-100 text-slate-600 ring-slate-200'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {$classes}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>{{ $label }}
</span>
