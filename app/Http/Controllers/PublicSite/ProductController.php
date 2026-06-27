<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\BookingAvailabilityService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_unless($product->status === 'active', 404);
        $product->load(['partner', 'category', 'images', 'reviews.customer']);

        return view('public.product', [
            'product' => $product,
            'similar' => Product::active()->where('category_id', $product->category_id)->whereKeyNot($product->id)
                ->with(['partner', 'primaryImage'])->limit(4)->get(),
        ]);
    }

    public function availability(Request $request, Product $product, BookingAvailabilityService $availability)
    {
        $data = $request->validate([
            'start_at' => ['required', 'date', 'after_or_equal:today'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json($availability->check($product, $data['start_at'], $data['end_at'], $data['qty']));
    }

    public function checkout(Request $request, Product $product, \App\Services\BookingPriceService $prices)
    {
        abort_unless($product->status === 'active', 404);
        $data = $request->validate([
            'start_at' => ['required', 'date', 'after_or_equal:today'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        return view('public.checkout', [
            'product' => $product->load('partner', 'primaryImage'),
            'bookingData' => $data,
            'price' => $prices->calculate($product, $data['start_at'], $data['end_at'], $data['qty']),
        ]);
    }
}
