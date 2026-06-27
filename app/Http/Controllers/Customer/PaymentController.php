<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentUploadRequest;
use App\Models\Booking;

class PaymentController extends Controller
{
    public function store(PaymentUploadRequest $request, Booking $booking)
    {
        $this->authorize('view', $booking);
        abort_unless(in_array($booking->status, ['confirmed', 'waiting_payment']), 422, 'Booking belum dapat dibayar.');

        $path = $request->file('proof_file')->store('payment-proofs', 'public');
        $booking->payments()->create([
            'payment_code' => 'PAY-'.now()->format('ymdHis').'-'.$booking->id,
            'method' => 'bank_transfer',
            'amount' => $booking->total_amount,
            'proof_file' => $path,
            'status' => 'waiting_confirmation',
        ]);
        $booking->update(['payment_status' => 'waiting_confirmation', 'status' => 'waiting_payment']);

        return back()->with('success', 'Bukti pembayaran terkirim dan sedang diverifikasi.');
    }
}
