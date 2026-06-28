<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingAvailabilityService;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::where('partner_id', auth()->user()->partnerProfile->id)
            ->with(['customer', 'items.product.primaryImage', 'payments'])
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(request('q'), fn ($q, $term) => $q->where('booking_code', 'like', "%{$term}%"))
            ->latest()->paginate(12)->withQueryString();

        return view('mitra.bookings', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('mitra.booking-show', [
            'booking' => $booking->load(['customer', 'partner', 'items.product.primaryImage', 'payments']),
        ]);
    }

    public function update(Booking $booking, BookingAvailabilityService $availability)
    {
        $this->authorize('view', $booking);
        $data = request()->validate([
            'status' => ['required', 'in:confirmed,ready_pickup,ongoing,returned,completed,cancelled'],
            'partner_notes' => ['nullable', 'string', 'max:1000'],
            'return_condition' => ['required_if:status,returned', 'nullable', 'string', 'max:2000'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'damage_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['status'] === 'confirmed') {
            $item = $booking->items()->with('product')->firstOrFail();
            $check = $availability->check($item->product, $item->start_at, $item->end_at, $item->quantity, $booking->id);
            if (! $check['available']) {
                throw ValidationException::withMessages(['status' => $check['message']]);
            }
        }

        if ($data['status'] === 'ready_pickup') {
            abort_unless($booking->payment_status === 'paid', 422, 'Pesanan hanya dapat disiapkan setelah pembayaran terverifikasi.');
        }

        if ($data['status'] === 'completed') {
            abort_unless($booking->payment_status === 'paid', 422, 'Seluruh tagihan harus lunas sebelum transaksi diselesaikan.');
        }

        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['ready_pickup', 'cancelled'],
            'ready_pickup' => ['ongoing'],
            'ongoing' => ['returned'],
            'returned' => ['completed'],
        ];
        abort_unless(in_array($data['status'], $allowed[$booking->status] ?? [], true), 422, 'Perubahan status tidak valid.');
        $timestamps = match ($data['status']) {
            'confirmed' => ['confirmed_at' => now()],
            'ready_pickup' => ['ready_pickup_at' => now()],
            'ongoing' => ['picked_up_at' => now()],
            'returned' => ['returned_at' => now()],
            'completed' => [
                'completed_at' => now(),
                'deposit_status' => (float) $booking->deposit_amount > 0 ? 'pending_refund' : 'not_applicable',
            ],
            'cancelled' => [
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'deposit_status' => 'not_applicable',
            ],
            default => [],
        };
        $fees = [];
        if ($data['status'] === 'returned') {
            $fees = [
                'late_fee' => $data['late_fee'] ?? 0,
                'damage_fee' => $data['damage_fee'] ?? 0,
            ];
            $extraFee = (float) $fees['late_fee'] + (float) $fees['damage_fee'];
            if ($extraFee > 0) {
                $fees['total_amount'] = (float) $booking->total_amount + $extraFee;
                $fees['payment_status'] = 'unpaid';
            }
        }
        $booking->update($data + $timestamps + $fees);
        $booking->items()->update(['item_status' => match ($data['status']) {
            'ready_pickup' => 'prepared',
            'ongoing' => 'ongoing',
            'returned' => 'returned',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'booked',
        }]);

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }
}
