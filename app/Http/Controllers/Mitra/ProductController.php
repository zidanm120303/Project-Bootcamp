<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('partner_id', auth()->user()->partnerProfile->id)
            ->with(['category', 'primaryImage'])
            ->withCount('bookingItems')
            ->withExists([
                'bookingItems as has_active_bookings' => fn ($query) => $query->whereHas(
                    'booking',
                    fn ($booking) => $booking->whereIn('status', \App\Models\Booking::ACTIVE_RENTAL_STATUSES)
                ),
            ])
            ->latest()->paginate(10);

        return view('mitra.products', compact('products'));
    }

    public function create()
    {
        return view('mitra.product-form', ['product' => new Product, 'categories' => Category::where('status', 'active')->get()]);
    }

    public function store(ProductStoreRequest $request)
    {
        if ($request->boolean('submit_review')) {
            $this->ensurePartnerVerified();
        }
        $data = $request->safe()->except(['image', 'submit_review']);
        $data['partner_id'] = $request->user()->partnerProfile->id;
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $data['status'] = $request->boolean('submit_review') ? 'pending_review' : 'draft';
        $product = Product::create($data);
        $this->storeImage($request, $product);
        $this->syncUnits($product);

        return redirect()->route('mitra.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('mitra.product-form', [
            'product' => $product->load('units'),
            'categories' => Category::where('status', 'active')->get(),
        ]);
    }

    public function update(ProductStoreRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        if ($request->boolean('submit_review')) {
            $this->ensurePartnerVerified();
        }
        $data = $request->safe()->except(['image', 'submit_review']);
        $data['status'] = $request->boolean('submit_review') ? 'pending_review' : $product->status;
        $product->update($data);
        $this->storeImage($request, $product);
        $this->syncUnits($product);

        return redirect()->route('mitra.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->forceDelete();

        return back()->with('success', 'Produk yang belum pernah disewa berhasil dihapus permanen.');
    }

    public function updateStatus(Product $product)
    {
        $this->authorize('changeStatus', $product);
        $data = request()->validate(['status' => ['required', 'in:active,inactive,archived']]);
        if ($data['status'] === 'active') {
            $this->ensurePartnerVerified();
        }
        $product->update($data);

        return back()->with('success', match ($data['status']) {
            'inactive' => 'Produk berhasil dinonaktifkan.',
            'archived' => 'Produk berhasil diarsipkan.',
            default => 'Produk berhasil diaktifkan.',
        });
    }

    private function storeImage(ProductStoreRequest $request, Product $product): void
    {
        if ($request->hasFile('image')) {
            $product->images()->updateOrCreate(
                ['is_primary' => true],
                ['image_path' => $request->file('image')->store('products', 'public'), 'sort_order' => 0]
            );
        }
    }

    private function syncUnits(Product $product): void
    {
        $current = $product->units()->count();

        for ($number = $current + 1; $number <= $product->stock_total; $number++) {
            $product->units()->create([
                'unit_code' => 'CAM-'.str_pad((string) $product->id, 3, '0', STR_PAD_LEFT).'-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'condition_status' => 'good',
                'availability_status' => 'available',
                'notes' => 'Unit aktif dan telah melewati pemeriksaan mitra.',
            ]);
        }

        if ($current > $product->stock_total) {
            $product->units()->orderByDesc('id')->limit($current - $product->stock_total)
                ->where('availability_status', 'available')
                ->update(['availability_status' => 'blocked', 'notes' => 'Unit dinonaktifkan karena penyesuaian stok.']);
        }
    }

    private function ensurePartnerVerified(): void
    {
        abort_unless(
            auth()->user()->partnerProfile?->verification_status === 'verified',
            422,
            'Profil dan dokumen mitra harus terverifikasi sebelum produk dapat diajukan atau dipublikasikan.'
        );
    }
}
