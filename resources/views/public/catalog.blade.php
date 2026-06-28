@extends('layouts.public')
@section('title', 'Katalog Rental Kamera — RentalPro')
@section('content')
    <section class="border-b border-slate-200 bg-gradient-to-br from-indigo-50 to-white py-10">
        <div class="container-app">
            <h1 class="section-title">Temukan peralatan produksi Anda</h1>
        </div>
    </section>

    <section class="container-app py-8">
        <form action="{{ route('catalog') }}" method="GET" class="grid gap-6 lg:grid-cols-[280px_1fr]">
            <aside class="card h-fit p-5 lg:sticky lg:top-24">
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-ink">Filter pencarian</h2>
                    <a href="{{ route('catalog') }}" class="text-xs font-bold text-indigo-600">Reset</a>
                </div>

                <div class="mt-5 space-y-5">
                    <div>
                        <label class="label">Kata kunci</label>
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="input"
                            placeholder="Kamera, merek, atau toko...">
                    </div>
                    <div>
                        <label class="label">Lokasi</label>
                        <select name="city" class="input">
                            <option value="">Semua lokasi</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Kategori</label>
                        <select name="category" class="input">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="type" value="rental">
                    <div>
                        <label class="label">Harga sewa per hari</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input name="min_price" value="{{ $filters['min_price'] ?? '' }}" type="text"
                                inputmode="numeric" data-rupiah class="input px-3" placeholder="Rp minimum">
                            <input name="max_price" value="{{ $filters['max_price'] ?? '' }}" type="text"
                                inputmode="numeric" data-rupiah class="input px-3" placeholder="Rp maksimum">
                        </div>
                    </div>
                    <div>
                        <label class="label">Rating minimal</label>
                        <select name="rating" class="input">
                            <option value="">Semua rating</option>
                            @foreach ([4, 4.5, 4.8] as $rating)
                                <option value="{{ $rating }}" @selected(($filters['rating'] ?? '') == $rating)>★ {{ $rating }}+
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-3 text-sm font-semibold text-slate-600">
                        <input type="checkbox" name="trusted" value="1" @checked($filters['trusted'] ?? false)
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Mitra terpercaya saja
                    </label>
                    <button class="btn-primary w-full">Terapkan filter</button>
                </div>
            </aside>

            <div>
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-500">
                        Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari
                        {{ $products->total() }} produk
                    </p>
                    <div class="flex items-center gap-2">
                        <label for="catalog-sort"
                            class="text-xs font-bold uppercase tracking-wider text-slate-400">Urutkan</label>
                        <select id="catalog-sort" name="sort" onchange="this.form.submit()"
                            class="rounded-xl border-slate-200 text-sm font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Terbaru</option>
                            <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Rating terbaik</option>
                            <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Harga terendah</option>
                            <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Harga tertinggi</option>
                        </select>
                    </div>
                </div>

                @if ($products->count())
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $products->links() }}</div>
                @else
                    <x-empty-state title="Produk tidak ditemukan"
                        description="Coba ubah kata kunci atau longgarkan filter pencarian Anda." />
                @endif
            </div>
        </form>
    </section>
@endsection
