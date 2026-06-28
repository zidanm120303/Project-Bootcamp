@extends('layouts.public')
@section('title', 'Checkout Penyewaan — RentalPro')
@section('content')
<section class="container-app py-10">
    <div class="mb-7">
        <p class="section-kicker">Validasi penyewa</p>
        <h1 class="section-title">Checkout penyewaan</h1>
        <p class="mt-2 text-sm text-slate-500">Lengkapi data pribadi yang akan diverifikasi saat pengambilan barang.</p>
    </div>

    <x-flash />

    <form action="{{ route('customer.bookings.store') }}" method="POST" enctype="multipart/form-data" class="grid items-start gap-6 lg:grid-cols-[1fr_390px]">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="start_at" value="{{ $bookingData['start_at'] }}">
        <input type="hidden" name="end_at" value="{{ $bookingData['end_at'] }}">
        <input type="hidden" name="quantity" value="{{ $bookingData['quantity'] }}">

        <div class="space-y-6">
            <section class="card p-6">
                <h2 class="text-lg font-extrabold text-ink">Data pribadi customer</h2>
                <p class="mt-2 text-sm text-slate-500">Data ini bersifat privat dan hanya dapat dilihat oleh Anda, mitra terkait, dan admin.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Nama lengkap</label>
                        <input name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" class="input" required>
                    </div>
                    <div>
                        <label class="label">Nomor HP / WhatsApp aktif</label>
                        <input name="customer_phone" value="{{ old('customer_phone', auth()->user()->phone) }}" class="input" placeholder="08xxxxxxxxxx" required>
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input name="customer_email" type="email" value="{{ old('customer_email', auth()->user()->email) }}" class="input" required>
                    </div>
                    <div>
                        <label class="label">Nomor KTP / SIM / Kartu Pelajar</label>
                        <input name="identity_number" value="{{ old('identity_number', auth()->user()->identity_number) }}" class="input" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Alamat lengkap</label>
                        <textarea name="customer_address" rows="3" class="input" required>{{ old('customer_address', collect([auth()->user()->address, auth()->user()->city, auth()->user()->province, auth()->user()->postal_code])->filter()->join(', ')) }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Upload foto identitas</label>
                        <input name="identity_file" type="file" accept=".jpg,.jpeg,.png,.pdf" class="input" @required(! auth()->user()->identity_file)>
                        @if(auth()->user()->identity_file)
                            <p class="mt-2 text-xs font-semibold text-emerald-600">Identitas dari profil akan digunakan. Pilih file baru hanya jika ingin menggantinya untuk transaksi ini.</p>
                        @else
                            <p class="mt-2 text-xs text-slate-400">Format JPG, PNG, atau PDF. Maksimal 2 MB. File disimpan secara privat.</p>
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Catatan tambahan</label>
                        <textarea name="customer_notes" rows="4" class="input" placeholder="Catatan untuk mitra (opsional)">{{ old('customer_notes') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="card p-6">
                <div class="flex items-start gap-4">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600"><x-icon name="store" /></span>
                    <div>
                        <h2 class="font-extrabold text-ink">Pengambilan langsung di toko mitra</h2>
                        <p class="mt-1 text-sm font-semibold leading-6 text-amber-700">Barang sewa wajib diambil langsung oleh customer di toko mitra sesuai jadwal sewa yang telah dipilih.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 rounded-2xl bg-slate-50 p-5 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Nama toko</p>
                        <p class="mt-1 font-extrabold text-ink">{{ $product->partner->business_name }}</p>
                        <p class="mt-4 text-xs text-slate-400">Alamat</p>
                        <p class="mt-1 leading-6">{{ $product->partner->address }}, {{ $product->partner->city }}, {{ $product->partner->province }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Kontak mitra</p>
                        <p class="mt-1 font-bold text-indigo-600">{{ $product->partner->phone }}</p>
                        <p class="mt-4 text-xs text-slate-400">Jam operasional</p>
                        <p class="mt-1 font-semibold">{{ $product->partner->operational_hours ?: 'Konfirmasi langsung kepada mitra.' }}</p>
                    </div>
                </div>
                <p class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">{{ $product->partner->pickup_note ?: 'Tunjukkan kode booking dan identitas asli saat mengambil barang.' }}</p>
            </section>
        </div>

        <aside class="card sticky top-24 overflow-hidden">
            <img src="{{ $product->image_url }}" class="aspect-[16/8] w-full object-cover" alt="{{ $product->name }}">
            <div class="p-6">
                <p class="text-xs font-bold text-indigo-600">{{ $product->partner->business_name }}</p>
                <h2 class="mt-2 text-lg font-black text-ink">{{ $product->name }}</h2>
                <div class="mt-5 space-y-3 border-y border-slate-100 py-5 text-sm">
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Tanggal sewa</span><span class="text-right font-bold text-slate-700">{{ \Carbon\Carbon::parse($bookingData['start_at'])->translatedFormat('d M Y') }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Tanggal kembali</span><span class="text-right font-bold text-slate-700">{{ \Carbon\Carbon::parse($bookingData['end_at'])->translatedFormat('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Durasi</span><span class="font-bold">{{ $price['rental_days'] }} hari</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Jumlah unit</span><span class="font-bold">{{ $bookingData['quantity'] }} unit</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Metode</span><span class="font-bold">Ambil di toko</span></div>
                </div>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between text-slate-500"><span>Subtotal</span><span>Rp{{ number_format($price['subtotal'], 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-slate-500"><span>Deposit keamanan</span><span>Rp{{ number_format($price['deposit'], 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-slate-500"><span>Biaya layanan</span><span>Rp{{ number_format($price['platform_fee'], 0, ',', '.') }}</span></div>
                    <div class="flex justify-between border-t border-slate-100 pt-4 text-lg font-black text-ink"><span>Total</span><span>Rp{{ number_format($price['total'], 0, ',', '.') }}</span></div>
                </div>
                <button class="btn-primary mt-6 w-full">Buat booking →</button>
                <p class="mt-3 text-center text-[11px] text-slate-400">Dengan melanjutkan, Anda menyetujui proses verifikasi identitas oleh mitra.</p>
            </div>
        </aside>
    </form>
</section>
@endsection
