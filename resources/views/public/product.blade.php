@extends('layouts.public')
@section('title', $product->name.' — RentalPro')
@section('content')
@php
$units = ['day' => 'hari', 'hour' => 'jam', 'week' => 'minggu', 'month' => 'bulan', 'service' => 'layanan', 'item' =>
'item'];
@endphp
<div class="container-app py-7">
    <nav class="mb-5 flex items-center gap-2 overflow-hidden text-xs text-slate-500">
        <a href="{{ route('home') }}">Beranda</a><span>›</span>
        <a href="{{ route('catalog', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
        <span>›</span><span class="truncate font-semibold text-slate-700">{{ $product->name }}</span>
    </nav>

    <div class="grid items-start gap-6 xl:grid-cols-[1fr_390px]">
        <div class="space-y-6">
            <section class="card overflow-hidden p-4 sm:p-6">
                <div class="grid gap-6 lg:grid-cols-[1.05fr_.95fr]">
                    <div>
                        <div class="relative overflow-hidden rounded-2xl bg-slate-100">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                class="aspect-[4/3] h-full w-full object-cover">
                            <span
                                class="absolute left-4 top-4 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white">Barang
                                Sewa</span>
                        </div>
                        <div class="mt-3 grid grid-cols-5 gap-2">
                            @foreach($product->images->take(5) as $image)
                            <img src="{{ str_starts_with($image->image_path, 'http') ? $image->image_path : asset('storage/'.$image->image_path) }}"
                                class="aspect-square rounded-xl border-2 border-indigo-500 object-cover" alt="">
                            @endforeach
                        </div>
                    </div>
                    <div class="py-1">
                        <span
                            class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700">{{ $product->category->name }}</span>
                        <h1 class="mt-5 text-3xl font-black leading-tight tracking-tight text-ink">{{ $product->name }}
                        </h1>
                        <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
                            <span class="flex items-center gap-1 font-bold text-amber-500">
                                <x-icon name="star" class="h-5 w-5 fill-current" />
                                {{ number_format($product->average_rating, 1) }}
                            </span>
                            <span class="h-4 w-px bg-slate-200"></span>
                            <span class="flex items-center gap-1 text-slate-500">
                                <x-icon name="location" class="h-4 w-4" />{{ $product->location_city }}
                            </span>
                        </div>
                        <div class="mt-5 flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-100 font-black text-indigo-700">{{ str($product->partner->business_name)->substr(0, 1) }}</span>
                            <div>
                                <p class="text-sm font-extrabold text-ink">{{ $product->partner->business_name }}</p>
                                <p class="mt-0.5 flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                    <x-icon name="shield" class="h-4 w-4" />Mitra Terpercaya
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            @foreach([['calendar','Jadwal Aman','Sistem cek bentrok'],['box','Ketersediaan','Diperiksa
                            otomatis'],['shield','Mitra Resmi','Data terverifikasi'],['store','Ambil di Toko','Lokasi
                            mitra']] as [$icon,$title,$text])
                            <div class="rounded-xl border border-slate-200 p-3">
                                <p class="flex items-center gap-2 text-xs font-extrabold text-ink">
                                    <x-icon :name="$icon" class="h-5 w-5 text-indigo-600" />{{ $title }}
                                </p>
                                <p class="mt-1 pl-7 text-[11px] text-slate-400">{{ $text }}</p>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-5 text-sm leading-6 text-slate-500">{{ $product->description }}</p>
                    </div>
                </div>
            </section>

            <section class="card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="section-kicker">Detail kamera</p>
                        <h2 class="text-lg font-extrabold text-ink">Spesifikasi dan kelengkapan</h2>
                    </div><span class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">Kondisi
                        {{ $product->condition_label }}</span>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach([
                    ['Merek', $product->brand],
                    ['Model', $product->model],
                    ['Jenis', $product->camera_type],
                    ['Sensor', $product->sensor_type],
                    ['Resolusi foto', $product->resolution_mp ? $product->resolution_mp.' MP' : null],
                    ['Resolusi video', $product->video_resolution],
                    ['Mount lensa', $product->lens_mount],
                    ] as [$label, $value])
                    @if($value)<div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs text-slate-400">{{ $label }}</p>
                        <p class="mt-1 text-sm font-extrabold text-ink">{{ $value }}</p>
                    </div>@endif
                    @endforeach
                </div>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-extrabold text-ink">Termasuk dalam paket</h3>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-500">
                            {{ $product->included_accessories }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-extrabold text-ink">Ketentuan sewa</h3>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-500">
                            {{ $product->rental_terms }}</p>
                    </div>
                </div>
            </section>

            <section class="card p-6">
                <h2 class="text-lg font-extrabold text-ink">Lokasi pengambilan barang</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-[1fr_220px]">
                    <div class="space-y-3 text-sm">
                        <p><span class="block text-xs text-slate-400">Toko mitra</span><strong
                                class="text-ink">{{ $product->partner->business_name }}</strong></p>
                        <p><span class="block text-xs text-slate-400">Alamat
                                lengkap</span>{{ $product->partner->address }}, {{ $product->partner->city }},
                            {{ $product->partner->province }} {{ $product->partner->postal_code }}</p>
                        <p><span class="block text-xs text-slate-400">Kontak / WhatsApp</span><a
                                class="font-bold text-indigo-600"
                                href="tel:{{ $product->partner->phone }}">{{ $product->partner->phone }}</a></p>
                    </div>
                    <div class="rounded-2xl bg-indigo-50 p-4 text-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-indigo-500">Jam operasional</p>
                        <p class="mt-2 font-extrabold text-ink">
                            {{ $product->partner->operational_hours ?: 'Hubungi mitra untuk konfirmasi jam buka.' }}</p>
                        <p class="mt-4 text-xs leading-5 text-slate-500">
                            {{ $product->partner->pickup_note ?: 'Tunjukkan kode booking saat mengambil barang.' }}</p>
                    </div>
                </div>
                <div
                    class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-800">
                    Barang sewa wajib diambil langsung oleh customer di toko mitra sesuai jadwal sewa yang telah
                    dipilih.
                </div>
            </section>

            <section class="card p-6">
                <h2 class="text-lg font-extrabold text-ink">Spesifikasi & ketentuan</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach(['Produk diperiksa sebelum disewa','Kondisi bersih dan siap pakai','Bawa identitas asli
                    saat pengambilan','Tunjukkan kode booking kepada mitra','Pengembalian dilakukan ke toko
                    mitra','Ikuti catatan pengambilan dari mitra'] as $item)
                    <p class="flex gap-2 text-sm text-slate-600"><span class="text-emerald-500">✓</span>{{ $item }}</p>
                    @endforeach
                </div>
            </section>

            <section class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-ink">Ulasan pelanggan</h2><span
                        class="text-2xl font-black text-ink">{{ number_format($product->average_rating, 1) }} <span
                            class="text-amber-400">★</span></span>
                </div>
                <div class="mt-5 space-y-4">
                    @forelse($product->reviews->take(3) as $review)
                    <div class="border-t border-slate-100 pt-4">
                        <div class="flex justify-between">
                            <p class="font-bold text-ink">{{ $review->customer->name }}</p>
                            <p class="text-amber-400">{{ str_repeat('★', $review->rating) }}</p>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $review->review_text }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500">Belum ada ulasan tertulis untuk produk ini.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside
            x-data="{start:'{{ now()->addDay()->format('Y-m-d') }}',end:'{{ now()->addDays(3)->format('Y-m-d') }}',quantity:1,checking:false,result:null,async check(){this.checking=true;this.result=null;let response=await fetch('{{ route('products.availability', $product) }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},body:JSON.stringify({start_at:this.start,end_at:this.end,quantity:this.quantity})});this.result=await response.json();this.checking=false}}"
            class="card sticky top-24 p-5 sm:p-6">
            <h2 class="text-xl font-black text-ink">Booking & jadwal</h2>
            <p class="mt-1 text-sm text-slate-500">Mulai dari <span
                    class="font-extrabold text-indigo-600">Rp{{ number_format($product->price, 0, ',', '.') }}</span> /
                {{ $units[$product->price_unit] }}</p>
            <form method="GET" action="{{ route('customer.checkout', $product) }}" class="mt-6 space-y-5">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="label">Tanggal sewa</label><input x-model="start" name="start_at" type="date"
                            min="{{ now()->format('Y-m-d') }}" class="input px-3" required></div>
                    <div><label class="label">Tanggal kembali</label><input x-model="end" name="end_at" type="date"
                            :min="start" class="input px-3" required></div>
                </div>
                <div>
                    <label class="label">Jumlah Unit Disewa</label>
                    <div class="inline-flex items-center overflow-hidden rounded-xl border border-slate-200">
                        <button type="button" @click="quantity=Math.max(1,quantity-1)"
                            class="px-4 py-3 font-bold">−</button>
                        <input x-model="quantity" name="quantity" readonly
                            class="w-14 border-0 p-0 text-center text-sm font-bold focus:ring-0">
                        <button type="button" @click="quantity=Math.min(99,quantity+1)"
                            class="px-4 py-3 font-bold">+</button>
                    </div>
                </div>
                <button type="button" @click="check" :disabled="checking" class="btn-secondary w-full"><span
                        x-text="checking ? 'Memeriksa...' : 'Cek ketersediaan'"></span></button>
                <div x-show="result" x-cloak class="rounded-xl p-4 text-sm"
                    :class="result?.available ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                    <p class="font-bold" x-text="result?.available ? 'Barang tersedia' : 'Barang tidak tersedia'"></p>
                    <p class="mt-1 text-xs" x-text="result?.message"></p>
                </div>
                <div class="space-y-2 border-t border-slate-100 pt-5">
                    <div class="flex justify-between text-sm text-slate-500"><span>Harga per
                            hari</span><span>Rp{{ number_format($product->price, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-sm text-slate-500"><span>Deposit per
                            unit</span><span>Rp{{ number_format($product->security_deposit, 0, ',', '.') }}</span></div>
                </div>
                <button @click.prevent="
        if (!result) { alert('Silakan cek ketersediaan terlebih dahulu!'); return; }
        if (!result.available) { return; }
        $el.closest('form').submit()
    " :class="result && !result.available ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'btn-primary'"
                    class="w-full py-3.5 rounded-xl font-bold transition-all">
                    Lanjut Ke Checkout →
                </button>
                <p class="text-center text-[11px] text-slate-400">Ketersediaan dan jumlah unit divalidasi kembali saat
                    checkout.</p>
            </form>
        </aside>
    </div>

    @if($similar->count())
    <section class="py-14">
        <h2 class="section-title">Produk serupa</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach($similar as $item)
            <x-product-card :product="$item" />@endforeach
        </div>
    </section>
    @endif
</div>
@endsection