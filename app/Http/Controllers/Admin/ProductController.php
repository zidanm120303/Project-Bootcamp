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
            ->latest()->paginate(12)->withQueryString();

        return view('admin.products', compact('products'));
    }

    public function update(Product $product)
    {
        $data = request()->validate([
            'status' => ['required', 'in:active,rejected,inactive,pending_review'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $product->update($data);

        return back()->with('success', 'Status produk berhasil diperbarui.');
    }
}
