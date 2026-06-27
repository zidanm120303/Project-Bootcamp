<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingStoreRequest;
use App\Models\Booking;
use App\Models\Product;
use App\Services\BookingAvailabilityService;
use App\Services\BookingPriceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::where('customer_id', auth()->id())
            ->with(['partner', 'items.product.primaryImage', 'payments'])
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()->paginate(10)->withQueryString();

        return view('customer.bookings', compact('bookings'));
    }

    public function store(BookingStoreRequest $request, BookingAvailabilityService $availability, BookingPriceService $prices)
    {
        $booking = DB::transaction(function () use ($request, $availability, $prices) {
            $product = Product::whereKey($request->integer('product_id'))->lockForUpdate()->firstOrFail();
            $startAt = Carbon::parse($request->start_at)->startOfDay();
            $endAt = Carbon::parse($request->end_at)->startOfDay();
            $qty = $request->integer('qty');
            $check = $availability->check($product, $startAt, $endAt, $qty);

            if (! $check['available']) {
                throw ValidationException::withMessages(['qty' => $check['message']]);
            }

            $price = $prices->calculate($product, $startAt, $endAt, $qty);
            $booking = Booking::create([
                'booking_code' => 'RTR-'.now()->format('ymd').'-'.strtoupper(substr((string) \Illuminate\Support\Str::uuid(), 0, 6)),
                'customer_id' => $request->user()->id,
                'partner_id' => $product->partner_id,
                'booking_type' => $product->product_type,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'subtotal_amount' => $price['subtotal'],
                'platform_fee' => $price['platform_fee'],
                'total_amount' => $price['total'],
                'customer_notes' => $request->customer_notes,
                'status' => 'pending',
            ]);

            $booking->items()->create([
                'product_id' => $product->id,
                'qty' => $qty,
                'price' => $product->price,
                'price_unit' => $product->price_unit,
                'duration' => $price['duration'],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'subtotal' => $price['subtotal'],
            ]);

            return $booking;
        }, 3);

        return redirect()->route('customer.bookings.show', $booking)->with('success', 'Booking berhasil dibuat. Menunggu konfirmasi mitra.');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('customer.booking-show', ['booking' => $booking->load(['partner.user', 'items.product.primaryImage', 'payments', 'reviews', 'disputes'])]);
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('view', $booking);
        abort_unless(in_array($booking->status, ['pending', 'confirmed', 'waiting_payment']), 422);
        $booking->update(['status' => 'cancelled', 'cancelled_by' => auth()->id(), 'cancelled_reason' => request('reason', 'Dibatalkan customer')]);

        return back()->with('success', 'Booking telah dibatalkan.');
    }
}
