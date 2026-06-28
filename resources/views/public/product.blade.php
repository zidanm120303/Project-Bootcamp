@extends('layouts.public')
@section('title', $product->name . ' — RentalPro')
@section('content')
    <div class="container-app py-7">
        <nav class="mb-5 flex items-center gap-2 overflow-hidden text-xs text-slate-500">
            <a href="{{ route('home') }}">Beranda</a><span>›</span>
            <a href="{{ route('catalog', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
            <span>›</span><span class="truncate font-semibold text-slate-700">{{ $product->name }}</span>
        </nav>

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
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
                                @foreach ($product->images->take(5) as $image)
                                    <img src="{{ str_starts_with($image->image_path, 'http') ? $image->image_path : asset('storage/' . $image->image_path) }}"
                                        class="aspect-square rounded-xl border-2 border-indigo-500 object-cover"
                                        alt="">
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
                                    <x-partner-name :partner="$product->partner" class="text-sm font-extrabold text-ink" />
                                    @if ($product->partner->verification_status === 'verified')
                                        <p class="mt-0.5 text-xs font-semibold text-emerald-600">Mitra Terverifikasi</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-5 grid grid-cols-2 gap-3">
                                @foreach ([
            ['calendar', 'Jadwal Aman', 'Sistem cek bentrok'],
            [
                'box',
                'Ketersediaan',
                'Diperiksa
                                otomatis',
            ],
            ['shield', 'Mitra Resmi', 'Data terverifikasi'],
            [
                'store',
                'Ambil di Toko',
                'Lokasi
                                mitra',
            ],
        ] as [$icon, $title, $text])
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
                        @foreach ([['Merek', $product->brand], ['Model', $product->model], ['Jenis', $product->camera_type], ['Sensor', $product->sensor_type], ['Resolusi foto', $product->resolution_mp ? $product->resolution_mp . ' MP' : null], ['Resolusi video', $product->video_resolution], ['Mount lensa', $product->lens_mount]] as [$label, $value])
                            @if ($value)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs text-slate-400">{{ $label }}</p>
                                    <p class="mt-1 text-sm font-extrabold text-ink">{{ $value }}</p>
                                </div>
                            @endif
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
                            <p><span class="block text-xs text-slate-400">Toko mitra</span><x-partner-name :partner="$product->partner"
                                    class="font-bold text-ink" /></p>
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
                                {{ $product->partner->operational_hours ?: 'Hubungi mitra untuk konfirmasi jam buka.' }}
                            </p>
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
                        @foreach ([
            'Produk diperiksa sebelum disewa',
            'Kondisi bersih dan siap pakai',
            'Bawa identitas asli
                        saat pengambilan',
            'Tunjukkan kode booking kepada mitra',
            'Pengembalian dilakukan ke toko
                        mitra',
            'Ikuti catatan pengambilan dari mitra',
        ] as $item)
                            <p class="flex gap-2 text-sm text-slate-600"><span
                                    class="text-emerald-500">✓</span>{{ $item }}</p>
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

            @php
                $bookingWidget = [
                    'url' => route('products.availability', $product),
                    'start' => now()->addDay()->format('Y-m-d'),
                    'end' => now()->addDays(3)->format('Y-m-d'),
                    'maxQuantity' => $product->stock_total,
                    'price' => (int) $product->price,
                    'deposit' => (int) $product->security_deposit,
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'calendar' => $availabilityCalendar,
                ];
            @endphp
            <aside x-data='bookingAvailability(@json($bookingWidget, JSON_HEX_APOS))' class="sticky top-24">
                <form method="GET" action="{{ route('customer.checkout', $product) }}" class="space-y-3">
                    <section class="card p-5 sm:p-6">
                        <h2 class="text-base font-black text-ink">Booking & Jadwal</h2>
                        <div class="mt-5 space-y-5">
                            <div>
                                <label class="label">Pilih Tanggal</label>
                                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 shadow-sm focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-100">
                                    <x-icon name="calendar" class="h-4 w-4 shrink-0 text-slate-500" />
                                    <input x-model="start" name="start_at" type="date" @change="changeDate()"
                                        min="{{ now()->format('Y-m-d') }}"
                                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-[11px] font-semibold text-slate-700 focus:ring-0"
                                        required>
                                    <span class="text-xs text-slate-400">–</span>
                                    <input x-model="end" name="end_at" type="date" @change="changeDate()"
                                        :min="start"
                                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-[11px] font-semibold text-slate-700 focus:ring-0"
                                        required>
                                    <span x-show="result?.available" x-cloak
                                        class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-[9px] font-bold text-emerald-700">Tersedia</span>
                                </div>
                            </div>
                            <div x-show="result" x-cloak class="flex items-start gap-3 rounded-xl border p-3.5"
                                :class="result?.available ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-rose-100 bg-rose-50 text-rose-700'">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-white/70">
                                    <x-icon name="shield" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-xs font-extrabold"
                                        x-text="result?.available ? 'Produk tersedia pada tanggal yang dipilih.' : 'Produk tidak tersedia pada tanggal yang dipilih.'"></p>
                                    <p class="mt-1 text-[10px] leading-4"
                                        x-text="result?.available ? 'Tidak ada bentrok pemesanan pada tanggal ini.' : result?.message"></p>
                                </div>
                            </div>
                            <div>
                                <label class="label">Durasi Sewa</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach ([2, 3, 4] as $duration)
                                        <button type="button" @click="setDuration({{ $duration }})"
                                            class="rounded-xl border px-2 py-3 text-left transition"
                                            :class="rentalDays === {{ $duration }} ?
                                                'border-indigo-500 bg-indigo-50 text-indigo-700 ring-2 ring-indigo-100' :
                                                'border-slate-200 bg-white text-slate-600 hover:border-indigo-200'">
                                            <span class="block text-xs font-extrabold">{{ $duration }} hari</span>
                                            <span class="mt-1 block text-[9px] text-slate-400"
                                                x-text="durationLabel({{ $duration }})"></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="label">Jumlah Unit</label>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="inline-flex items-center overflow-hidden rounded-xl border border-slate-200">
                                        <button type="button" @click="changeQuantity(-1)"
                                            class="px-4 py-2.5 font-bold text-slate-500">−</button>
                                        <input x-model="quantity" name="quantity" readonly
                                            class="w-12 border-0 p-0 text-center text-sm font-bold focus:ring-0">
                                        <button type="button" @click="changeQuantity(1)"
                                            class="px-4 py-2.5 font-bold text-slate-500">+</button>
                                    </div>
                                    <p class="text-[10px] font-bold text-emerald-600"
                                        x-text="`Stok Tersedia: ${result?.available_units ?? {{ $product->stock_total }}} unit`"></p>
                                </div>
                            </div>
                            <section class="rounded-2xl border border-slate-200 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-xs font-extrabold text-ink">Kalender Ketersediaan <span x-text="calendarTitle"></span></h3>
                                </div>
                                <div class="mt-3 grid grid-cols-[28px_repeat(7,minmax(0,1fr))] overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                                    <button type="button" @click="previousCalendar()" :disabled="calendarPage === 0"
                                        class="grid place-items-center border-r border-slate-100 text-slate-400 disabled:opacity-40">‹</button>
                                    <template x-for="day in visibleCalendar" :key="day.date">
                                        <button type="button" @click="selectDay(day)"
                                            :disabled="['past', 'unavailable'].includes(day.status)"
                                            :title="`${day.available_units} unit tersedia`"
                                            class="px-1 py-2 text-center transition"
                                            :class="isSelected(day) ?
                                                'bg-indigo-600 text-white' :
                                                day.status === 'past' ? 'cursor-not-allowed text-slate-300' :
                                                day.status === 'unavailable' ? 'cursor-not-allowed text-rose-500' :
                                                'text-slate-500 hover:bg-white'">
                                            <span class="block text-[8px] font-semibold" x-text="day.day_label"></span>
                                            <span class="mt-1 block text-[11px] font-black" x-text="day.date_label"></span>
                                            <span class="mx-auto mt-1 block h-1.5 w-1.5 rounded-full"
                                                :class="isSelected(day) ? 'bg-white' : day.status === 'available' ?
                                                    'bg-emerald-400' : day.status === 'limited' ? 'bg-amber-400' : day
                                                    .status === 'past' ? 'bg-slate-300' : 'bg-rose-400'"></span>
                                        </button>
                                    </template>
                                </div>
                                <div class="mt-3 flex justify-between gap-2 text-[9px] text-slate-500">
                                    <span class="flex items-center gap-1"><i
                                            class="h-1.5 w-1.5 rounded-full bg-emerald-400"></i>Tersedia</span>
                                    <span class="flex items-center gap-1"><i
                                            class="h-1.5 w-1.5 rounded-full bg-amber-400"></i>Terbatas</span>
                                    <span class="flex items-center gap-1"><i
                                            class="h-1.5 w-1.5 rounded-full bg-rose-400"></i>Tidak tersedia</span>
                                </div>
                            </section>

                            <section x-show="result && !result.available && result?.suggestions?.length" x-cloak>
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <h3 class="text-xs font-extrabold text-ink">Tanggal tersedia terdekat</h3>
                                    <span class="text-[10px] text-slate-400">Durasi tetap sama</span>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="suggestion in result?.suggestions ?? []" :key="suggestion.start_at">
                                        <button type="button" @click="selectSuggestion(suggestion)"
                                            class="flex w-full items-center justify-between gap-3 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2.5 text-left transition hover:border-emerald-300">
                                            <span>
                                                <span class="block text-xs font-extrabold text-emerald-800"
                                                    x-text="`${suggestion.start_label} – ${suggestion.end_label}`"></span>
                                                <span class="mt-0.5 block text-[10px] text-emerald-600"
                                                    x-text="`${suggestion.duration} hari · ${suggestion.available_units} unit tersedia`"></span>
                                            </span>
                                            <span class="text-sm font-black text-emerald-600">→</span>
                                        </button>
                                    </template>
                                </div>
                            </section>

                        </div>
                    </section>

                    <section class="card p-5 sm:p-6">
                        <h2 class="text-base font-black text-ink">Ringkasan Harga</h2>
                        <div class="mt-5 space-y-3 text-sm">
                            <div class="flex justify-between gap-4 text-slate-500">
                                <span>Harga Sewa (<span x-text="rentalDays"></span> hari)</span>
                                <span x-text="money(rentalSubtotal)"></span>
                            </div>
                            <div class="flex justify-between gap-4 text-slate-500">
                                <span>Jumlah Unit</span>
                                <span class="font-semibold text-slate-700">× <span x-text="quantity"></span></span>
                            </div>
                            <div class="flex justify-between gap-4 border-t border-slate-100 pt-3 text-slate-600">
                                <span>Subtotal</span>
                                <span class="font-bold text-ink" x-text="money(rentalSubtotal)"></span>
                            </div>
                            <div class="flex justify-between gap-4 text-slate-500">
                                <span>Deposit Keamanan</span>
                                <span x-text="money(depositTotal)"></span>
                            </div>
                            <div
                                class="flex justify-between gap-4 border-t border-slate-200 pt-4 text-lg font-black text-ink">
                                <span>Total Pembayaran</span>
                                <span x-text="money(bookingTotal)"></span>
                            </div>
                        </div>
                        <p class="mt-3 flex items-center gap-2 text-[11px] font-semibold text-emerald-600">
                            <x-icon name="shield" class="h-4 w-4" /> Pembayaran aman & terlindungi
                        </p>
                        <button type="button" @click="proceed($el.closest('form'))"
                            :class="result && !result.available ? 'bg-slate-200 text-slate-400 cursor-not-allowed' :
                                'btn-primary'"
                            class="mt-5 w-full rounded-xl py-3.5 font-bold transition-all">
                            Booking Sekarang <span>›</span>
                        </button>
                        <button type="button" @click="toggleFavorite()"
                            class="btn-secondary mt-2 w-full py-3 text-indigo-600">
                            <span class="text-lg leading-none" x-text="favorite ? '♥' : '♡'"></span>
                            <span x-text="favorite ? 'Tersimpan di Favorit' : 'Simpan ke Favorit'"></span>
                        </button>
                    </section>
                </form>

                <section class="card mt-4 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                                <x-icon name="shield" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold text-indigo-700">Butuh bantuan?</p>
                                <p class="mt-0.5 truncate text-[10px] text-slate-400">Tim kami siap membantu Anda.</p>
                            </div>
                        </div>
                        <a href="mailto:support@rentalpro.test"
                            class="btn-secondary shrink-0 px-3 py-2 text-[10px] text-indigo-600">Hubungi Kami</a>
                    </div>
                </section>
            </aside>
        </div>

        <section class="card mt-6 p-5">
            <div class="grid items-center gap-5 lg:grid-cols-[230px_repeat(4,minmax(0,1fr))]">
                <div>
                    <h2 class="text-sm font-black text-ink">Alur pemesanan RentalPro</h2>
                    <p class="mt-1 text-[11px] text-slate-400">Proses aman, mudah, dan transparan.</p>
                </div>
                @foreach ([
            ['store', '1', 'Diajukan', 'Pilih produk dan ajukan pesanan'],
            ['card', '2', 'Dibayar', 'Lakukan pembayaran untuk konfirmasi'],
            ['box', '3', 'Diproses', 'Mitra menyiapkan pesanan Anda'],
            ['shield', '4', 'Selesai', 'Ambil dan kembalikan sesuai jadwal'],
        ] as [$icon, $number, $title, $description])
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-indigo-50 text-indigo-600">
                            <x-icon :name="$icon" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-xs font-black text-ink">{{ $number }} {{ $title }}</p>
                            <p class="mt-1 text-[10px] leading-4 text-slate-400">{{ $description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        @if ($similar->count())
            <section class="py-14">
                <h2 class="section-title">Produk serupa</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
