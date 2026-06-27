@extends('layouts.dashboard')
@section('title','Produk & Jasa')
@section('page-title','Produk & Jasa')
@section('page-subtitle','Kelola katalog, harga, stok, dan status produk.')
@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500">{{ $products->total() }} produk dalam katalog Anda.</p><a href="{{ route('mitra.products.create') }}" class="btn-primary">+ Tambah produk</a></div>
@if($products->count())<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">@foreach($products as $product)
<article class="card overflow-hidden"><img src="{{ $product->image_url }}" class="aspect-[16/8] w-full object-cover"><div class="p-5"><div class="flex items-center justify-between"><span class="text-xs font-bold text-indigo-600">{{ $product->category->name }}</span><x-status-badge :status="$product->status" /></div><h2 class="mt-3 text-lg font-black text-ink">{{ $product->name }}</h2><div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-xs"><div><p class="text-slate-400">Harga</p><p class="mt-1 font-extrabold text-ink">Rp{{ number_format($product->price,0,',','.') }}</p></div><div><p class="text-slate-400">Stok</p><p class="mt-1 font-extrabold text-ink">{{ $product->stock_total }} unit</p></div></div><div class="mt-4 flex gap-2"><a href="{{ route('mitra.products.edit',$product) }}" class="btn-secondary flex-1 py-2">Edit</a><form action="{{ route('mitra.products.destroy',$product) }}" method="POST">@csrf @method('DELETE')<button class="btn-danger py-2" onclick="return confirm('Hapus produk ini?')">Hapus</button></form></div></div></article>
@endforeach</div><div class="mt-6">{{ $products->links() }}</div>@else<x-empty-state title="Belum ada produk" description="Tambahkan produk atau jasa pertama Anda untuk mulai menerima booking."><x-slot:action><a href="{{ route('mitra.products.create') }}" class="btn-primary">Tambah produk</a></x-slot:action></x-empty-state>@endif
@endsection
