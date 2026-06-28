<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\PartnerProfile;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'partnersPending' => PartnerProfile::where('verification_status', 'pending')->count(),
                'productsPending' => Product::where('status', 'pending_review')->count(),
                'paymentsPending' => Payment::where('status', 'waiting_confirmation')->count(),
                'complaints' => Dispute::whereNotIn('status', ['resolved', 'rejected'])->count(),
            ],
            'partners' => PartnerProfile::where('verification_status', 'pending')->with(['user', 'documents'])->latest()->limit(5)->get(),
            'products' => Product::where('status', 'pending_review')->with(['partner', 'category', 'primaryImage'])->latest()->limit(5)->get(),
            'bookings' => Booking::with(['customer', 'partner', 'items.product'])->latest()->limit(6)->get(),
            'revenue' => Booking::where('payment_status', 'paid')->sum('platform_fee'),
        ]);
    }
}
