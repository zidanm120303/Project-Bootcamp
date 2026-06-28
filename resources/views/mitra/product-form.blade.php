@extends('layouts.dashboard')
@section('title', $product->exists ? 'Edit Kamera' : 'Tambah Kamera')
@section('page-title', $product->exists ? 'Edit Kamera Rental' : 'Tambah Kamera Rental')
@section('page-subtitle', 'Lengkapi identitas kamera, spesifikasi, unit fisik, harga, dan ketentuan sewa.')
@section('content')
<form action="{{ $product->exists ? route('mitra.products.update', $product) : route('mitra.products.store') }}" method="POST" enctype="multipart/form-data" class="grid items-start gap-6 xl:grid-cols-[1fr_330px]">
    @csrf
    @if($product->exists) @method('PUT') @endif
    <div class="space-y-6">
        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Identitas kamera</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="label">Nama listing</label><input name="name" value="{{ old('name', $product->name) }}" class="input" placeholder="Contoh: Sony A7 IV Full Frame Kit 24-70mm" required></div>
                <div><label class="label">Merek</label><input name="brand" value="{{ old('brand', $product->brand) }}" class="input" placeholder="Sony" required></div>
                <div><label class="label">Model</label><input name="model" value="{{ old('model', $product->model) }}" class="input" placeholder="A7 IV" required></div>
                <div><label class="label">Kategori</label><select name="category_id" class="input" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div><label class="label">Jenis kamera/peralatan</label><select name="camera_type" class="input" required>@foreach(['Mirrorless','DSLR','Cinema Camera','Action Camera','Drone','Lensa','Lighting','Audio','Stabilizer','Aksesori'] as $type)<option value="{{ $type }}" @selected(old('camera_type', $product->camera_type) === $type)>{{ $type }}</option>@endforeach</select></div>
                <input type="hidden" name="product_type" value="rental">
                <input type="hidden" name="price_unit" value="day">
                <div class="sm:col-span-2"><label class="label">Deskripsi lengkap</label><textarea name="description" rows="5" class="input" required>{{ old('description', $product->description) }}</textarea></div>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Spesifikasi teknis</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div><label class="label">Sensor</label><input name="sensor_type" value="{{ old('sensor_type', $product->sensor_type) }}" class="input" placeholder="Full Frame CMOS"></div>
                <div><label class="label">Resolusi foto (MP)</label><input type="number" step="0.01" name="resolution_mp" value="{{ old('resolution_mp', $product->resolution_mp) }}" class="input" placeholder="33"></div>
                <div><label class="label">Resolusi video</label><input name="video_resolution" value="{{ old('video_resolution', $product->video_resolution) }}" class="input" placeholder="4K 60fps 10-bit"></div>
                <div><label class="label">Mount lensa</label><input name="lens_mount" value="{{ old('lens_mount', $product->lens_mount) }}" class="input" placeholder="Sony E-Mount"></div>
                <div><label class="label">Kondisi</label><select name="condition_label" class="input" required>@foreach(['Seperti Baru','Sangat Baik','Baik','Layak Pakai'] as $condition)<option value="{{ $condition }}" @selected(old('condition_label', $product->condition_label) === $condition)>{{ $condition }}</option>@endforeach</select></div>
                <div><label class="label">Nilai penggantian</label><input type="number" name="replacement_value" value="{{ old('replacement_value', $product->replacement_value) }}" class="input" min="0" placeholder="Nilai kamera"></div>
                <div class="sm:col-span-2 lg:col-span-3"><label class="label">Perlengkapan yang termasuk</label><textarea name="included_accessories" rows="4" class="input" placeholder="Body kamera, lensa, 2 baterai, charger, memory card, tas..." required>{{ old('included_accessories', $product->included_accessories) }}</textarea></div>
                <div class="sm:col-span-2 lg:col-span-3"><label class="label">Ketentuan penyewaan</label><textarea name="rental_terms" rows="4" class="input" placeholder="Dilarang terkena air, identitas asli wajib ditunjukkan..." required>{{ old('rental_terms', $product->rental_terms) }}</textarea></div>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-extrabold text-ink">Harga, stok, dan lokasi</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div><label class="label">Harga sewa per hari</label><div class="flex items-center gap-2"><span class="text-slate-600 font-semibold">Rp</span><input type="text" inputmode="numeric" value="{{ old('price', $product->price) ? number_format($product->price, 0, ',', '.') : '' }}" class="input flex-1 currency-display" placeholder="0" required></div><input type="hidden" name="price" class="currency-value"></div>
                <div><label class="label">Deposit keamanan per unit</label><div class="flex items-center gap-2"><span class="text-slate-600 font-semibold">Rp</span><input type="text" inputmode="numeric" value="{{ old('security_deposit', $product->security_deposit ?? 0) ? number_format($product->security_deposit, 0, ',', '.') : '' }}" class="input flex-1 currency-display" placeholder="0"></div><input type="hidden" name="security_deposit" class="currency-value"></div>
                <div><label class="label">Jumlah unit fisik</label><input type="number" name="stock_total" value="{{ old('stock_total', $product->stock_total ?: 1) }}" class="input" min="1" required></div>
                <div><label class="label">Minimal durasi (hari)</label><input type="number" name="min_rent_duration" value="{{ old('min_rent_duration', $product->min_rent_duration ?: 1) }}" class="input" min="1" required></div>
                <div><label class="label">Kota pengambilan</label><input name="location_city" value="{{ old('location_city', $product->location_city) }}" class="input" required></div>
                <div><label class="label">Alamat / area</label><input name="location_address" value="{{ old('location_address', $product->location_address) }}" class="input"></div>
                <div class="sm:col-span-2"><label class="label">Foto utama</label><input type="file" name="image" class="input" accept=".jpg,.jpeg,.png"><p class="mt-2 text-xs text-slate-400">JPG/PNG maksimal 3 MB. Gunakan foto asli kamera dengan rasio 4:3.</p></div>
            </div>
        </section>

        @if($product->exists && $product->units->count())
            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 p-5"><h2 class="font-extrabold text-ink">Daftar unit fisik</h2><p class="mt-1 text-xs text-slate-500">Kode unit dibuat otomatis berdasarkan jumlah stok.</p></div>
                <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Kode unit</th><th>Serial</th><th>Kondisi</th><th>Ketersediaan</th></tr></thead><tbody>@foreach($product->units as $unit)<tr><td class="font-bold text-indigo-600">{{ $unit->unit_code }}</td><td>{{ $unit->serial_number ?: '-' }}</td><td>{{ str($unit->condition_status)->replace('_', ' ')->title() }}</td><td>{{ str($unit->availability_status)->replace('_', ' ')->title() }}</td></tr>@endforeach</tbody></table></div>
            </section>
        @endif
    </div>

    <aside class="card sticky top-24 p-5">
        <h2 class="font-extrabold text-ink">Publikasi</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Simpan draf untuk dilanjutkan nanti atau ajukan kepada admin setelah seluruh data lengkap.</p>
        @if(auth()->user()->partnerProfile?->verification_status !== 'verified')
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-700"><p class="font-extrabold">Verifikasi mitra diperlukan</p><p class="mt-1 text-xs">Lengkapi profil dan dokumen, lalu tunggu persetujuan admin sebelum mengajukan produk.</p><a href="{{ route('mitra.profile.edit') }}" class="mt-2 inline-flex text-xs font-bold text-amber-800">Lengkapi dokumen →</a></div>
        @endif
        @if($product->exists)
            <div class="mt-4 rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Status saat ini</p><div class="mt-2"><x-status-badge :status="$product->status" /></div>@if($product->admin_notes)<p class="mt-3 text-xs text-rose-600">{{ $product->admin_notes }}</p>@endif</div>
        @endif
        <div class="mt-5 space-y-3"><button name="submit_review" value="1" class="btn-primary w-full" @disabled(auth()->user()->partnerProfile?->verification_status !== 'verified')>Ajukan untuk ditinjau</button><button name="submit_review" value="0" class="btn-secondary w-full">Simpan draf</button></div>
    </aside>
</form>

<script>
document.querySelectorAll('.currency-display').forEach(display => {
    const hiddenInput = display.parentElement.nextElementSibling;
    
    if (display.value) {
        hiddenInput.value = display.value.replace(/[^0-9]/g, '');
    }
    
    display.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, '');
        
        if (value) {
            this.value = parseInt(value).toLocaleString('id-ID');
            hiddenInput.value = value;
        } else {
            this.value = '';
            hiddenInput.value = '';
        }
    });
    
    display.addEventListener('blur', function() {
        if (this.value && !isNaN(parseInt(this.value.replace(/[^0-9]/g, '')))) {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = parseInt(value).toLocaleString('id-ID');
            hiddenInput.value = value;
        }
    });
});

document.querySelector('form').addEventListener('submit', function(e) {
    document.querySelectorAll('.currency-display').forEach(display => {
        const hiddenInput = display.parentElement.nextElementSibling;
        let value = display.value.replace(/[^0-9]/g, '');
        hiddenInput.value = value;
    });
});
</script>
@endsection
