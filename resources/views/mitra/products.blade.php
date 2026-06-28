@extends('layouts.dashboard')
@section('title', 'Kamera & Unit')
@section('page-title', 'Kamera & Unit')
@section('page-subtitle', 'Kelola katalog kamera, unit fisik, stok, dan keamanan penghapusan data.')
@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500">{{ $products->total() }} produk dalam katalog Anda.</p>
    <a href="{{ route('mitra.products.create') }}" class="btn-primary">+ Tambah produk</a>
</div>

@if($products->count())
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($products as $product)
            <article class="card overflow-hidden">
                <img src="{{ $product->image_url }}" class="aspect-[16/8] w-full object-cover" alt="">
                <div class="p-5">
                    <div class="flex items-center justify-between"><span class="text-xs font-bold text-indigo-600">{{ $product->category->name }}</span><x-status-badge :status="$product->status" /></div>
                    <h2 class="mt-3 text-lg font-black text-ink">{{ $product->name }}</h2>
                    <div class="mt-4 grid grid-cols-3 gap-3 rounded-xl bg-slate-50 p-3 text-xs">
                        <div><p class="text-slate-400">Harga</p><p class="mt-1 truncate font-extrabold text-ink">Rp{{ number_format($product->price, 0, ',', '.') }}</p></div>
                        <div><p class="text-slate-400">Stok</p><p class="mt-1 font-extrabold text-ink">{{ $product->stock_total }} unit</p></div>
                        <div><p class="text-slate-400">Riwayat</p><p class="mt-1 font-extrabold text-ink">{{ $product->booking_items_count }} booking</p></div>
                    </div>

                    @if($product->has_active_bookings)
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-700">
                            Produk memiliki booking aktif sehingga tidak dapat dihapus atau dinonaktifkan.
                        </div>
                    @elseif($product->booking_items_count > 0)
                        <form action="{{ route('mitra.products.status', $product) }}" method="POST" class="mt-4 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="min-w-0 flex-1 rounded-xl border-slate-200 py-2 text-xs">
                                <option value="active" @selected($product->status === 'active')>Aktif</option>
                                <option value="inactive" @selected($product->status === 'inactive')>Nonaktif</option>
                                <option value="archived" @selected($product->status === 'archived')>Arsipkan</option>
                            </select>
                            <button class="rounded-xl bg-indigo-600 px-4 text-xs font-bold text-white">Simpan status</button>
                        </form>
                        <p class="mt-2 text-[11px] text-slate-400">Produk pernah disewa sehingga tidak dapat dihapus permanen.</p>
                    @endif

                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('mitra.products.edit', $product) }}" class="btn-secondary flex-1 py-2">Edit</a>
                        @if(! $product->has_active_bookings && $product->booking_items_count === 0)
                            <form action="{{ route('mitra.products.destroy', $product) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger py-2" onclick="return confirm('Produk belum pernah disewa. Hapus permanen produk ini?')">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@else
    <x-empty-state title="Belum ada produk" description="Tambahkan produk atau jasa pertama Anda untuk mulai menerima booking.">
        <x-slot:action><a href="{{ route('mitra.products.create') }}" class="btn-primary">Tambah produk</a></x-slot:action>
    </x-empty-state>
@endif
@endsection
