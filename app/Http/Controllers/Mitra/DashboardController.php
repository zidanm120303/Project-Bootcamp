<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $partner = auth()->user()->partnerProfile;
        $bookings = Booking::where('partner_id', $partner->id);

        return view('mitra.dashboard', [
            'partner' => $partner->load('documents'),
            'recentBookings' => (clone $bookings)->with(['customer', 'items.product'])->latest()->limit(6)->get(),
            'lowStockProducts' => Product::where('partner_id', $partner->id)->orderBy('stock_total')->limit(5)->get(),
            'stats' => [
                'products' => Product::where('partner_id', $partner->id)->where('status', 'active')->count(),
                'today' => (clone $bookings)->whereDate('created_at', today())->count(),
                'revenue' => (clone $bookings)->whereIn('status', ['paid', 'prepared', 'ongoing', 'completed'])->sum('subtotal_amount'),
                'pending' => (clone $bookings)->where('status', 'pending')->count(),
            ],
        ]);
    }
}
