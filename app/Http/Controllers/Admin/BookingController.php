<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['customer', 'partner', 'items.product', 'payments'])
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(request('q'), fn ($q, $term) => $q->where('booking_code', 'like', "%{$term}%"))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.bookings', compact('bookings'));
    }
}
