<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['partner', 'category', 'primaryImage'])
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(request('q'), fn ($query, $term) => $query->where(function ($subQuery) use ($term) {
                $subQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%");
            }))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.products', compact('products'));
    }

    public function show(Product $product)
    {
        return view('admin.product-show', [
            'product' => $product->load(['partner.user', 'category', 'images', 'units']),
        ]);
    }

    public function update(Product $product)
    {
        $data = request()->validate([
            'status' => ['required', 'in:active,rejected,inactive,archived,pending_review'],
            'admin_notes' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
        ]);
        if ($data['status'] === 'active') {
            abort_unless(
                $product->partner->verification_status === 'verified',
                422,
                'Produk hanya dapat dipublikasikan setelah mitra terverifikasi.'
            );
        }
        $product->update($data);

        return back()->with('success', 'Status produk berhasil diperbarui.');
    }
}
