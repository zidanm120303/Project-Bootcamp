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

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('admin.booking-show', [
            'booking' => $booking->load(['customer', 'partner.user', 'items.product.primaryImage', 'payments']),
        ]);
    }

    public function refundDeposit(Booking $booking)
    {
        abort_unless($booking->status === 'completed', 422, 'Deposit hanya dapat dikembalikan setelah transaksi selesai.');
        abort_unless((float) $booking->deposit_amount > 0, 422, 'Transaksi ini tidak memiliki deposit.');
        abort_unless($booking->deposit_status === 'pending_refund', 422, 'Deposit tidak sedang menunggu pengembalian.');
        $booking->update([
            'deposit_status' => 'refunded',
            'deposit_refunded_at' => now(),
        ]);

        return back()->with('success', 'Deposit telah ditandai sebagai dikembalikan kepada customer.');
    }
}
