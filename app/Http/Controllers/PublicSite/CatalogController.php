<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\Rupiah;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:rental'],
            'min_price' => ['nullable', 'string', 'max:30'],
            'max_price' => ['nullable', 'string', 'max:30'],
            'rating' => ['nullable', 'numeric', 'between:1,5'],
            'trusted' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:latest,rating,price_low,price_high'],
        ]);

        $minPrice = Rupiah::value($filters['min_price'] ?? null);
        $maxPrice = Rupiah::value($filters['max_price'] ?? null);
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $sort = $filters['sort'] ?? 'latest';
        $products = Product::query()
            ->active()
            ->with(['partner', 'category', 'primaryImage'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('partner', fn ($partner) => $partner->where('business_name', 'like', "%{$term}%"))))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('location_city', $city))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('product_type', $type))
            ->when($minPrice !== null, fn ($q) => $q->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn ($q) => $q->where('price', '<=', $maxPrice))
            ->when($filters['rating'] ?? null, fn ($q, $rating) => $q->where('average_rating', '>=', $rating))
            ->when($request->boolean('trusted'), fn ($q) => $q->whereHas('partner', fn ($p) => $p->where('is_trusted', true)))
            ->when($sort === 'price_low', fn ($q) => $q->orderBy('price')->orderByDesc('id'))
            ->when($sort === 'price_high', fn ($q) => $q->orderByDesc('price')->orderByDesc('id'))
            ->when($sort === 'rating', fn ($q) => $q->orderByDesc('average_rating')->orderByDesc('total_reviews')->orderByDesc('id'))
            ->when($sort === 'latest', fn ($q) => $q->latest())
            ->paginate(9)
            ->withQueryString();

        return view('public.catalog', [
            'products' => $products,
            'categories' => Category::where('status', 'active')->get(),
            'cities' => Product::active()->select('location_city')->distinct()->orderBy('location_city')->pluck('location_city'),
            'filters' => array_merge($filters, [
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
            ]),
        ]);
    }
}
