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
            ->with(['category', 'primaryImage'])->latest()->paginate(10);

        return view('mitra.products', compact('products'));
    }

    public function create()
    {
        return view('mitra.product-form', ['product' => new Product, 'categories' => Category::where('status', 'active')->get()]);
    }

    public function store(ProductStoreRequest $request)
    {
        $data = $request->safe()->except(['image', 'submit_review']);
        $data['partner_id'] = $request->user()->partnerProfile->id;
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $data['status'] = $request->boolean('submit_review') ? 'pending_review' : 'draft';
        $product = Product::create($data);
        $this->storeImage($request, $product);

        return redirect()->route('mitra.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('mitra.product-form', ['product' => $product, 'categories' => Category::where('status', 'active')->get()]);
    }

    public function update(ProductStoreRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $data = $request->safe()->except(['image', 'submit_review']);
        $data['status'] = $request->boolean('submit_review') ? 'pending_review' : ($product->status === 'active' ? 'active' : 'draft');
        $product->update($data);
        $this->storeImage($request, $product);

        return redirect()->route('mitra.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
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
}
