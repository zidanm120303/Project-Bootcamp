<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\TrustedPartnerScoreService;

class ReviewController extends Controller
{
    public function store(Booking $booking, TrustedPartnerScoreService $trustedScore)
    {
        $this->authorize('view', $booking);
        abort_unless($booking->status === 'completed', 422, 'Ulasan hanya dapat diberikan setelah pesanan selesai.');
        $data = request()->validate(['rating' => ['required', 'integer', 'between:1,5'], 'review_text' => ['nullable', 'string', 'max:1500']]);
        $item = $booking->items()->firstOrFail();

        $review = \App\Models\Review::updateOrCreate(
            ['booking_id' => $booking->id, 'product_id' => $item->product_id],
            $data + ['customer_id' => auth()->id(), 'partner_id' => $booking->partner_id]
        );
        $product = $item->product;
        $product->update([
            'average_rating' => round($product->reviews()->avg('rating'), 2),
            'total_reviews' => $product->reviews()->count(),
        ]);
        $trustedScore->recalculate($booking->partner);

        return back()->with('success', 'Terima kasih, ulasan Anda telah diterbitkan.');
    }
}
