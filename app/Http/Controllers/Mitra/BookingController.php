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
            ->latest()->paginate(12)->withQueryString();

        return view('mitra.bookings', compact('bookings'));
    }

    public function update(Booking $booking, BookingAvailabilityService $availability)
    {
        $this->authorize('view', $booking);
        $data = request()->validate([
            'status' => ['required', 'in:waiting_payment,rejected,prepared,ongoing,completed'],
            'partner_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === 'waiting_payment') {
            $item = $booking->items()->with('product')->firstOrFail();
            $check = $availability->check($item->product, $item->start_at, $item->end_at, $item->qty, $booking->id);
            if (! $check['available']) {
                throw ValidationException::withMessages(['status' => $check['message']]);
            }
        }

        $allowed = [
            'pending' => ['waiting_payment', 'rejected'],
            'waiting_payment' => ['rejected'],
            'paid' => ['prepared'],
            'prepared' => ['ongoing'],
            'ongoing' => ['completed'],
        ];
        abort_unless(in_array($data['status'], $allowed[$booking->status] ?? [], true), 422, 'Perubahan status tidak valid.');
        $booking->update($data);
        $booking->items()->update(['item_status' => match ($data['status']) {
            'prepared' => 'prepared', 'ongoing' => 'ongoing', 'completed' => 'completed',
            'rejected' => 'cancelled', default => 'booked',
        }]);

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }
}
