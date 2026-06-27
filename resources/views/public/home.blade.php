@extends('layouts.public')
@section('title', 'Rentalpro — Sewa Mudah, Jadwal Aman')
@section('content')
    <section class="relative overflow-hidden bg-gradient-to-b from-indigo-50/70 via-white to-white py-12 lg:py-16">
        <div class="pointer-events-none absolute -left-24 top-20 h-72 w-72 rounded-full bg-sky-200/30 blur-3xl"></div>
        <div class="pointer-events-none absolute right-0 top-0 h-96 w-96 rounded-full bg-indigo-200/40 blur-3xl"></div>
        <div class="container-app relative grid items-center gap-12 lg:grid-cols-[1.05fr_.95fr]">
            <div>
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-white px-4 py-2 text-xs font-bold text-indigo-700 shadow-sm"><x-icon
                        name="shield" class="h-4 w-4" />Marketplace rental UMKM terpercaya</span>
                <h1 class="mt-6 text-4xl font-black leading-[1.1] tracking-[-0.04em] text-ink sm:text-5xl xl:text-[58px]">
                    Sewa mudah, jadwal aman, <span
                        class="bg-gradient-to-r from-indigo-600 to-blue-500 bg-clip-text text-transparent">bisnis makin
                        berkembang.</span></h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-500 sm:text-lg">Temukan barang dan jasa berkualitas
                    dari mitra UMKM terverifikasi. Jadwal bebas bentrok, stok akurat, dan transaksi terasa ringan.</p>
                <form action="{{ route('catalog') }}"
                    class="mt-8 grid gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-indigo-100/50 sm:grid-cols-[1fr_190px_auto]">
                    <label class="flex items-center gap-3 px-3"><x-icon name="search"
                            class="h-5 w-5 text-indigo-500" /><span class="sr-only">Cari produk</span><input name="q"
                            class="w-full border-0 px-0 py-3 text-sm focus:ring-0"
                            placeholder="Cari kamera, tenda, catering..." /></label>
                    <select name="city"
                        class="rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua lokasi</option>
                        <option>Jakarta Selatan</option>
                        <option>Depok</option>
                        <option>Bandung</option>
                        <option>Bogor</option>
                    </select>
                    <button class="btn-primary px-7"><x-icon name="search" class="h-5 w-5" />Cari</button>
                </form>
                <div class="mt-6 flex flex-wrap gap-x-6 gap-y-3 text-xs font-semibold text-slate-600">
                    <span class="flex items-center gap-2"><span
                            class="grid h-7 w-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600"><x-icon
                                name="calendar" class="h-4 w-4" /></span>Jadwal aman</span>
                    <span class="flex items-center gap-2"><span
                            class="grid h-7 w-7 place-items-center rounded-lg bg-amber-50 text-amber-600"><x-icon
                                name="box" class="h-4 w-4" /></span>Stok real-time</span>
                    <span class="flex items-center gap-2"><span
                            class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600"><x-icon
                                name="shield" class="h-4 w-4" /></span>Mitra terverifikasi</span>
                </div>
            </div>
            <div class="relative mx-auto w-full max-w-xl">
                <div class="absolute -inset-8 rounded-full bg-indigo-200/40 blur-2xl"></div>
                <div
                    class="relative overflow-hidden rounded-[32px] border-[10px] border-white bg-slate-100 shadow-2xl shadow-indigo-200">
                    <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=90"
                        alt="Perlengkapan acara UMKM" class="aspect-[4/3] w-full object-cover">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/80 to-transparent p-6 pt-20">
                        <p class="text-lg font-extrabold text-white">Semua kebutuhan acara, satu tempat.</p>
                        <p class="mt-1 text-sm text-white/70">Terawat • Terjadwal • Terlindungi</p>
                    </div>
                </div>
                <div
                    class="absolute -left-4 top-10 rounded-2xl border border-white bg-white/95 p-3 shadow-xl backdrop-blur">
                    <p class="flex items-center gap-2 text-xs font-extrabold text-emerald-700"><x-icon name="shield"
                            class="h-5 w-5" />Mitra Terpercaya</p>
                </div>
                <div
                    class="absolute -right-3 bottom-20 rounded-2xl border border-white bg-white/95 p-3 shadow-xl backdrop-blur">
                    <p class="flex items-center gap-2 text-xs font-extrabold text-indigo-700"><x-icon name="calendar"
                            class="h-5 w-5" />Tidak ada bentrok</p>
                </div>
            </div>
        </div>
    </section>

    <section id="kategori" class="container-app py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-kicker">Kategori populer</p>
                <h2 class="section-title">Ada untuk setiap kebutuhan</h2>
            </div><a href="{{ route('catalog') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">Lihat
                semua katalog →</a>
        </div>
        <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            @foreach ($categories as $category)
                <a href="{{ route('catalog', ['category' => $category->slug]) }}"
                    class="group rounded-2xl border border-slate-200 bg-white p-4 text-center transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-100">
                    <span
                        class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white"><x-icon
                            name="{{ in_array($category->icon, ['store', 'camera']) ? 'box' : ($category->icon === 'party' ? 'star' : 'grid') }}" /></span>
                    <h3 class="mt-3 text-sm font-extrabold leading-tight text-ink">{{ $category->name }}</h3>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $category->products_count }} pilihan</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="container-app">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="section-kicker">Rekomendasi terbaik</p>
                    <h2 class="section-title">Pilihan yang siap dipesan</h2>
                </div><a href="{{ route('catalog') }}" class="btn-secondary py-2.5">Jelajahi katalog</a>
            </div>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    <section id="cara-kerja" class="container-app py-20">
        <div class="text-center">
            <p class="section-kicker">Mudah dan transparan</p>
            <h2 class="section-title">Empat langkah menuju acara yang lancar</h2>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-4">
            @foreach ([['search', 'Temukan kebutuhan', 'Cari dan filter berdasarkan lokasi, kategori, serta anggaran.'], ['calendar', 'Pilih jadwal', 'Cek stok dan slot tersedia secara otomatis.'], ['card', 'Konfirmasi pesanan', 'Mitra mengonfirmasi, lalu unggah bukti pembayaran.'], ['shield', 'Selesai dengan tenang', 'Pantau progres hingga pesanan tuntas dan beri ulasan.']] as $i => [$icon, $title, $desc])
                <div class="card relative p-6"><span
                        class="absolute right-5 top-5 text-4xl font-black text-slate-100">0{{ $i + 1 }}</span><span
                        class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-600"><x-icon
                            :name="$icon" /></span>
                    <h3 class="mt-5 font-extrabold text-ink">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="mitra" class="container-app pb-20">
        <div
            class="overflow-hidden rounded-[28px] bg-gradient-to-br from-ink via-indigo-950 to-indigo-800 p-7 text-white sm:p-10 lg:flex lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-indigo-300">Untuk pelaku UMKM</p>
                <h2 class="mt-3 text-3xl font-black">Ubah inventaris menjadi peluang.</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-indigo-100/70">Kelola produk, stok, jadwal, booking, dan
                    pembayaran dalam satu dashboard yang sederhana.</p>
            </div>
            <a href="{{ route('register', ['role' => 'mitra']) }}"
                class="mt-7 inline-flex rounded-xl bg-white px-6 py-3 text-sm font-extrabold text-indigo-700 shadow-xl transition hover:-translate-y-0.5 lg:mt-0">Daftar
                sebagai mitra →</a>
        </div>
    </section>
@endsection
