@extends('layouts.public')
@section('title', 'Katalog Produk & Jasa — Rentra UMKM')
@section('content')
<section class="border-b border-slate-200 bg-gradient-to-br from-indigo-50 to-white py-10">
    <div class="container-app"><p class="section-kicker">Marketplace UMKM</p><h1 class="section-title">Temukan kebutuhan terbaik Anda</h1><p class="mt-3 text-sm text-slate-500">{{ $products->total() }} produk dan jasa tersedia dari mitra terverifikasi.</p></div>
</section>
<section class="container-app py-8">
    <form action="{{ route('catalog') }}" class="grid gap-6 lg:grid-cols-[260px_1fr]">
        <aside class="card h-fit p-5 lg:sticky lg:top-24">
            <div class="flex items-center justify-between"><h2 class="font-extrabold text-ink">Filter pencarian</h2><a href="{{ route('catalog') }}" class="text-xs font-bold text-indigo-600">Reset</a></div>
            <div class="mt-5 space-y-5">
                <div><label class="label">Kata kunci</label><input name="q" value="{{ request('q') }}" class="input" placeholder="Cari produk..."></div>
                <div><label class="label">Lokasi</label><select name="city" class="input"><option value="">Semua lokasi</option>@foreach($cities as $city)<option @selected(request('city') === $city)>{{ $city }}</option>@endforeach</select></div>
                <div><label class="label">Kategori</label><select name="category" class="input"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach</select></div>
                <div><label class="label">Jenis</label><div class="grid grid-cols-3 gap-2">@foreach(['rental'=>'Sewa','service'=>'Jasa','sale'=>'Jual'] as $value=>$label)<label class="cursor-pointer"><input class="peer sr-only" type="radio" name="type" value="{{ $value }}" @checked(request('type')===$value)><span class="block rounded-lg border border-slate-200 px-2 py-2 text-center text-xs font-bold peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700">{{ $label }}</span></label>@endforeach</div></div>
                <div><label class="label">Rentang harga</label><div class="grid grid-cols-2 gap-2"><input name="min_price" value="{{ request('min_price') }}" type="number" class="input px-3" placeholder="Minimum"><input name="max_price" value="{{ request('max_price') }}" type="number" class="input px-3" placeholder="Maksimum"></div></div>
                <div><label class="label">Rating minimal</label><select name="rating" class="input"><option value="">Semua rating</option>@foreach([4,4.5,4.8] as $rating)<option value="{{ $rating }}" @selected(request('rating')==$rating)>★ {{ $rating }}+</option>@endforeach</select></div>
                <label class="flex items-center gap-3 text-sm font-semibold text-slate-600"><input type="checkbox" name="trusted" value="1" @checked(request('trusted')) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">Mitra terpercaya saja</label>
                <button class="btn-primary w-full">Terapkan filter</button>
            </div>
        </aside>
        <div>
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><p class="text-sm font-semibold text-slate-500">Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }}</p><select name="sort" onchange="this.form.submit()" class="rounded-xl border-slate-200 text-sm font-semibold focus:border-indigo-500 focus:ring-indigo-500"><option value="latest">Terbaru</option><option value="rating" @selected(request('sort')==='rating')>Rating terbaik</option><option value="price_low" @selected(request('sort')==='price_low')>Harga terendah</option><option value="price_high" @selected(request('sort')==='price_high')>Harga tertinggi</option></select></div>
            @if($products->count())
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>
                <div class="mt-8">{{ $products->links() }}</div>
            @else
                <x-empty-state title="Produk tidak ditemukan" description="Coba ubah kata kunci atau longgarkan filter pencarian Anda." />
            @endif
        </div>
    </form>
</section>
@endsection
