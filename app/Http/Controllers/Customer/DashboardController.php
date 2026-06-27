<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $bookings = Booking::where('customer_id', auth()->id())->with(['partner', 'items.product'])->latest();

        return view('customer.dashboard', [
            'recentBookings' => (clone $bookings)->limit(5)->get(),
            'stats' => [
                'active' => (clone $bookings)->whereIn('status', ['confirmed', 'waiting_payment', 'paid', 'prepared', 'ongoing'])->count(),
                'waitingPayment' => (clone $bookings)->where('payment_status', 'unpaid')->count(),
                'completed' => (clone $bookings)->where('status', 'completed')->count(),
                'reviews' => \App\Models\Review::where('customer_id', auth()->id())->count(),
            ],
        ]);
    }
}
