<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PartnerProfile;
use App\Models\Product;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('public.home', [
            'categories' => Category::where('status', 'active')->withCount(['products' => fn ($q) => $q->active()])->limit(8)->get(),
            'products' => Product::active()->with(['partner', 'category', 'primaryImage'])->orderByDesc('is_featured')->latest()->limit(8)->get(),
            'trustedPartners' => PartnerProfile::where('is_trusted', true)->withCount('products')->limit(4)->get(),
            'stats' => [
                'partners' => PartnerProfile::where('verification_status', 'verified')->count(),
                'products' => Product::active()->count(),
                'bookings' => \App\Models\Booking::where('status', 'completed')->count(),
            ],
        ]);
    }
}
