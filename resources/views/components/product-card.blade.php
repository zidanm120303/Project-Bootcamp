@props(['product'])
@php $units = ['day' => 'hari', 'hour' => 'jam', 'week' => 'minggu', 'month' => 'bulan', 'service' => 'layanan', 'item' => 'item']; @endphp
<article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-100/60">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/3] overflow-hidden bg-slate-100">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        <span class="absolute left-3 top-3 rounded-lg bg-white/95 px-2.5 py-1 text-[11px] font-extrabold text-indigo-700 shadow-sm">{{ $product->camera_type ?: 'Kamera Rental' }}</span>
    </a>
    <div class="p-4">
        <div class="flex items-center gap-1.5 text-xs text-slate-500"><x-icon name="store" class="h-4 w-4 shrink-0" /><x-partner-name :partner="$product->partner" /></div>
        <a href="{{ route('products.show', $product) }}"><h3 class="mt-2 min-h-12 text-base font-extrabold leading-snug text-ink group-hover:text-indigo-600">{{ $product->name }}</h3></a>
        <p class="mt-1 truncate text-xs text-slate-400">{{ $product->brand }} {{ $product->model }} · {{ $product->condition_label }}</p>
        <p class="mt-3 text-lg font-black text-ink">Rp{{ number_format($product->price, 0, ',', '.') }} <span class="text-xs font-medium text-slate-500">/ {{ $units[$product->price_unit] }}</span></p>
        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
            <span class="flex items-center gap-1 font-bold text-amber-500"><x-icon name="star" class="h-4 w-4 fill-current" />{{ number_format($product->average_rating, 1) }} <span class="font-medium text-slate-400">({{ $product->total_reviews }})</span></span>
            <span class="flex items-center gap-1 text-slate-500"><x-icon name="location" class="h-4 w-4" />{{ $product->location_city }}</span>
        </div>
    </div>
</article>
