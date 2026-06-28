@extends('layouts.dashboard')
@section('title','Dashboard Mitra')
@section('page-title','Dashboard Mitra')
@section('page-subtitle','Ringkasan operasional '.$partner->business_name)
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card label="Produk aktif" :value="$stats['products']" icon="box" />
    <x-stat-card label="Booking hari ini" :value="$stats['today']" icon="calendar" tone="emerald" />
    <x-stat-card label="Total pendapatan" :value="'Rp'.number_format($stats['revenue'],0,',','.')" icon="chart" tone="violet" />
    <x-stat-card label="Menunggu konfirmasi" :value="$stats['pending']" icon="alert" tone="amber" hint="Perlu tindakan Anda" />
</div>
<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 class="font-extrabold text-ink">Pesanan masuk</h2>
                <p class="mt-1 text-xs text-slate-500">Tinjau permintaan terbaru customer</p>
            </div><a href="{{ route('mitra.bookings.index') }}" class="text-sm font-bold text-indigo-600">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Produk</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $booking)<tr>
                        <td>
                            <p class="font-bold text-ink">{{ $booking->customer_name ?: $booking->customer->name }}</p>
                            <p class="text-xs text-slate-400">{{ $booking->booking_code }}</p>
                        </td>
                        <td>
                            <p class="max-w-[180px] truncate font-semibold">{{ $booking->items->first()->product->name }}</p>
                            <p class="text-xs text-slate-400">{{ $booking->start_at->translatedFormat('d M') }}–{{ $booking->end_at->translatedFormat('d M') }}</p>
                        </td>
                        <td class="font-bold text-ink">Rp{{ number_format($booking->total_amount,0,',','.') }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td>@if($booking->status==='pending')<div class="flex gap-2">
                                <form method="POST" action="{{ route('mitra.bookings.update',$booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white">Konfirmasi</button></form>
                                <form method="POST" action="{{ route('mitra.bookings.update',$booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600">Batalkan</button></form>
                            </div>@else<a href="{{ route('mitra.bookings.show',$booking) }}" class="text-xs font-bold text-indigo-600">Detail</a>@endif</td>
                    </tr>@empty<tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">Belum ada booking masuk.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </section>
    <aside class="card p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-ink">Kalender ketersediaan</h2><span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-600">{{ $availabilityMonth }}</span>
        </div>
        <p class="mt-1 text-[11px] text-slate-400">Warna dihitung dari stok, jadwal produk, blackout, dan booking aktif.</p>
        <div class="mt-4 grid grid-cols-7 gap-1 text-center text-xs">
            @foreach(['S','S','R','K','J','S','M'] as $day)
                <span class="py-2 font-bold text-slate-400">{{ $day }}</span>
            @endforeach
            @foreach($availabilityCalendar as $day)
                <span title="{{ $day['available_units'] }} dari {{ $day['capacity'] }} unit tersedia"
                    class="relative grid aspect-square place-items-center rounded-lg font-semibold
                        {{ $day['is_today']
                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200'
                            : ($day['status'] === 'available'
                                ? 'bg-emerald-50 text-emerald-700'
                                : ($day['status'] === 'limited'
                                    ? 'bg-amber-50 text-amber-700'
                                    : ($day['status'] === 'unavailable'
                                        ? 'bg-rose-50 text-rose-500'
                                        : 'bg-slate-50 text-slate-300'))) }}
                        {{ $day['is_current_month'] ? '' : 'opacity-40' }}">
                    {{ $day['date_label'] }}
                    @if(!$day['is_today'] && in_array($day['status'], ['limited', 'unavailable'], true))
                        <i class="absolute bottom-1 h-1 w-1 rounded-full {{ $day['status'] === 'limited' ? 'bg-amber-400' : 'bg-rose-400' }}"></i>
                    @endif
                </span>
            @endforeach
        </div>
        <div class="mt-5 flex flex-wrap justify-between gap-2 text-[10px] text-slate-500">
            <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-emerald-400"></i>Tersedia</span>
            <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-amber-400"></i>Terbatas</span>
            <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-rose-400"></i>Penuh/tutup</span>
            <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-indigo-500"></i>Hari ini</span>
        </div>
    </aside>
</div>
<div class="mt-6 grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
    <section class="card p-5 lg:col-span-1">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-ink">Manajemen stok</h2>
        </div>
        <div class="mt-4 space-y-3">@foreach($lowStockProducts as $product)<div class="flex items-center gap-3"><img src="{{ $product->image_url }}" class="h-10 w-12 rounded-lg object-cover">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-ink">{{ $product->name }}</p>
                    <p class="text-[11px] text-slate-400">{{ $product->product_type }}</p>
                </div><span class="text-sm font-black {{ $product->stock_total<=2?'text-amber-600':'text-emerald-600' }}">{{ $product->stock_total }} unit</span>
            </div>@endforeach</div>
    </section>
    <section class="card p-5"><span class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-600"><x-icon name="calendar" /></span>
        <h2 class="mt-4 font-extrabold text-ink">Validasi jadwal otomatis</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Sistem mencegah stok terjual atau tersewa melebihi kapasitas pada periode yang sama.</p>
        <p class="mt-4 text-xs font-bold text-emerald-600">● Aktif</p>
    </section>
    @php $latestDocs = $partner->documents->sortByDesc('id')->groupBy('document_type')->map->first(); @endphp
    <section class="card p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-ink">Status verifikasi</h2><x-status-badge :status="$partner->verification_status" />
        </div>
        <div class="mt-4 space-y-3">@foreach(['ktp'=>'KTP pemilik','nib'=>'NIB usaha','rekening'=>'Rekening bank','foto_usaha'=>'Foto lokasi'] as $type=>$item)@php $document = $latestDocs->get($type); @endphp<p class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 text-sm font-semibold text-slate-600"><span class="{{ $document?->status === 'approved' ? 'text-emerald-500' : 'text-amber-500' }}">{{ $document?->status === 'approved' ? '✓' : '!' }}</span>{{ $item }}<span class="ml-auto text-[10px] text-slate-400">{{ $document ? str($document->status)->replace('_',' ')->title() : 'Belum ada' }}</span></p>@endforeach</div><a href="{{ route('mitra.profile.edit') }}" class="btn-secondary mt-4 w-full py-2">Lihat dokumen</a>
    </section>
</div>
@endsection
