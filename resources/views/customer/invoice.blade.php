<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $booking->booking_code }} — RentalPro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .invoice { box-shadow: none !important; border: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8">
@php $item = $booking->items->first(); @endphp
<div class="no-print mx-auto mb-4 flex max-w-4xl justify-between px-4">
    <a href="{{ route('customer.bookings.show', $booking) }}" class="btn-secondary">← Kembali</a>
    <button onclick="window.print()" class="btn-primary">Cetak invoice</button>
</div>
<main class="invoice mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl sm:p-10">
    <header class="flex flex-wrap items-start justify-between gap-6 border-b border-slate-200 pb-7">
        <x-application-logo />
        <div class="text-right">
            <p class="text-xs font-bold uppercase tracking-[.2em] text-slate-400">Invoice Penyewaan</p>
            <h1 class="mt-2 text-2xl font-black text-indigo-600">{{ $booking->booking_code }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $booking->created_at->translatedFormat('d F Y, H:i') }}</p>
        </div>
    </header>

    <section class="grid gap-8 border-b border-slate-200 py-7 sm:grid-cols-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Penyewa</p>
            <p class="mt-3 font-black text-ink">{{ $booking->customer_name }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $booking->customer_phone }} • {{ $booking->customer_email }}</p>
            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $booking->customer_address }}</p>
            <p class="mt-2 text-xs text-slate-400">Identitas: {{ $booking->identity_number }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi Pengambilan</p>
            <p class="mt-3 font-black text-ink">{{ $booking->partner->business_name }}</p>
            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $booking->partner->address }}, {{ $booking->partner->city }}, {{ $booking->partner->province }}</p>
            <p class="mt-2 text-sm font-bold text-indigo-600">{{ $booking->partner->phone }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $booking->partner->operational_hours ?: 'Jam operasional perlu dikonfirmasi kepada mitra.' }}</p>
        </div>
    </section>

    <section class="py-7">
        <div class="overflow-hidden rounded-xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="p-4">Barang</th><th class="p-4">Jadwal</th><th class="p-4 text-center">Unit</th><th class="p-4 text-right">Subtotal</th></tr></thead>
                <tbody><tr class="border-t border-slate-200"><td class="p-4"><p class="font-bold text-ink">{{ $item->product->name }}</p><p class="text-xs text-slate-400">Rp{{ number_format($item->price_per_unit, 0, ',', '.') }} / hari × {{ $item->rental_days }} hari</p></td><td class="p-4">{{ $booking->start_at->translatedFormat('d M Y') }}<br><span class="text-slate-400">s.d. {{ $booking->end_at->translatedFormat('d M Y') }}</span></td><td class="p-4 text-center font-bold">{{ $item->quantity }}</td><td class="p-4 text-right font-bold text-ink">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td></tr></tbody>
            </table>
        </div>
        <div class="ml-auto mt-6 max-w-sm space-y-3 text-sm">
            <div class="flex justify-between text-slate-500"><span>Subtotal</span><span>Rp{{ number_format($booking->subtotal_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-slate-500"><span>Deposit keamanan</span><span>Rp{{ number_format($booking->deposit_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-slate-500"><span>Biaya layanan</span><span>Rp{{ number_format($booking->platform_fee, 0, ',', '.') }}</span></div>
            <div class="flex justify-between border-t border-slate-200 pt-4 text-lg font-black text-ink"><span>Total</span><span>Rp{{ number_format($booking->total_amount, 0, ',', '.') }}</span></div>
        </div>
    </section>

    <section class="rounded-xl bg-indigo-50 p-5 text-sm leading-6 text-indigo-900">
        <p class="font-extrabold">Petunjuk pengambilan</p>
        <p class="mt-1">Barang wajib diambil langsung di toko mitra. Tunjukkan kode <strong>{{ $booking->booking_code }}</strong> dan identitas asli yang sesuai dengan data penyewa.</p>
        <p class="mt-2 text-xs text-indigo-700">{{ $booking->pickup_note ?: $booking->partner->pickup_note }}</p>
    </section>
</main>
</body>
</html>
