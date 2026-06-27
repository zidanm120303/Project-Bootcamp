<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->active()
            ->with(['partner', 'category', 'primaryImage'])
            ->when($request->q, fn ($q, $term) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")))
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->city, fn ($q, $city) => $q->where('location_city', $city))
            ->when($request->type, fn ($q, $type) => $q->where('product_type', $type))
            ->when($request->min_price, fn ($q, $price) => $q->where('price', '>=', $price))
            ->when($request->max_price, fn ($q, $price) => $q->where('price', '<=', $price))
            ->when($request->rating, fn ($q, $rating) => $q->where('average_rating', '>=', $rating))
            ->when($request->boolean('trusted'), fn ($q) => $q->whereHas('partner', fn ($p) => $p->where('is_trusted', true)))
            ->when($request->sort === 'price_low', fn ($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_high', fn ($q) => $q->orderByDesc('price'))
            ->when($request->sort === 'rating', fn ($q) => $q->orderByDesc('average_rating'))
            ->when(! in_array($request->sort, ['price_low', 'price_high', 'rating']), fn ($q) => $q->latest())
            ->paginate(9)
            ->withQueryString();

        return view('public.catalog', [
            'products' => $products,
            'categories' => Category::where('status', 'active')->get(),
            'cities' => Product::active()->select('location_city')->distinct()->orderBy('location_city')->pluck('location_city'),
        ]);
    }
}
