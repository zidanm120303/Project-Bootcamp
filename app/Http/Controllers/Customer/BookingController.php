<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingStoreRequest;
use App\Models\Booking;
use App\Models\Product;
use App\Services\BookingAvailabilityService;
use App\Services\BookingCodeService;
use App\Services\BookingPriceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

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

    public function store(
        BookingStoreRequest $request,
        BookingAvailabilityService $availability,
        BookingPriceService $prices,
        BookingCodeService $bookingCodes
    ) {
        if ($request->hasFile('identity_file')) {
            $identityPath = $request->file('identity_file')->store('customer-identities');
        } else {
            abort_unless(
                $request->user()->identity_file && Storage::exists($request->user()->identity_file),
                422,
                'Dokumen identitas profil tidak ditemukan. Silakan unggah ulang melalui profil akun.'
            );
            $extension = pathinfo($request->user()->identity_file, PATHINFO_EXTENSION);
            $identityPath = 'customer-identities/'.Str::uuid().'.'.$extension;
            Storage::copy($request->user()->identity_file, $identityPath);
        }

        try {
            $booking = DB::transaction(function () use (
                $request,
                $availability,
                $prices,
                $bookingCodes,
                $identityPath
            ) {
                $product = Product::with('partner')->whereKey($request->integer('product_id'))->lockForUpdate()->firstOrFail();
                $startAt = Carbon::parse($request->start_at)->startOfDay();
                $endAt = Carbon::parse($request->end_at)->startOfDay();
                $quantity = $request->integer('quantity');
                $check = $availability->check($product, $startAt, $endAt, $quantity);

                if (! $check['available']) {
                    throw ValidationException::withMessages(['quantity' => $check['message']]);
                }

                $price = $prices->calculate($product, $startAt, $endAt, $quantity);
                $booking = Booking::create([
                    'booking_code' => $bookingCodes->generate(),
                    'customer_id' => $request->user()->id,
                    'partner_id' => $product->partner_id,
                    'booking_type' => $product->product_type,
                    'pickup_method' => 'store_pickup',
                    'pickup_note' => $product->partner->pickup_note,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'customer_address' => $request->customer_address,
                    'identity_number' => $request->identity_number,
                    'identity_file' => $identityPath,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'subtotal_amount' => $price['subtotal'],
                    'deposit_amount' => $price['deposit'],
                    'platform_fee' => $price['platform_fee'],
                    'total_amount' => $price['total'],
                    'deposit_status' => $price['deposit'] > 0 ? 'pending' : 'not_applicable',
                    'customer_notes' => $request->customer_notes,
                    'status' => 'pending',
                ]);

                $booking->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price_per_unit' => $product->price,
                    'price_unit' => $product->price_unit,
                    'rental_days' => $price['rental_days'],
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'subtotal' => $price['subtotal'],
                ]);

                return $booking;
            }, 3);
        } catch (Throwable $exception) {
            if ($identityPath) {
                Storage::delete($identityPath);
            }

            throw $exception;
        }

        return redirect()->route('customer.bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_code} berhasil dibuat. Menunggu konfirmasi mitra.");
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('customer.booking-show', ['booking' => $booking->load(['partner.user', 'items.product.primaryImage', 'payments', 'reviews', 'disputes'])]);
    }

    public function invoice(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('customer.invoice', [
            'booking' => $booking->load(['customer', 'partner.user', 'items.product', 'payments']),
        ]);
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('view', $booking);
        abort_unless(in_array($booking->status, ['pending', 'confirmed']), 422);
        abort_unless(in_array($booking->payment_status, ['unpaid', 'rejected']), 422, 'Pesanan yang sudah dibayar tidak dapat dibatalkan langsung.');
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'deposit_status' => 'not_applicable',
            'cancelled_by' => auth()->id(),
            'cancelled_reason' => request('reason', 'Dibatalkan customer'),
        ]);

        return back()->with('success', 'Booking telah dibatalkan.');
    }
}
