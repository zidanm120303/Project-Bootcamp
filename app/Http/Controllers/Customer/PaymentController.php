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
        abort_unless(in_array($booking->status, ['confirmed', 'returned']), 422, 'Pembayaran belum dapat dilakukan pada tahap ini.');
        abort_if($booking->payments()->where('status', 'waiting_confirmation')->exists(), 422, 'Bukti pembayaran sebelumnya masih ditinjau.');
        abort_unless($booking->outstanding_amount > 0, 422, 'Tidak ada tagihan yang perlu dibayar.');

        $path = $request->file('proof_file')->store('payment-proofs');
        $booking->payments()->create([
            'payment_code' => 'PAY-'.now()->format('ymdHis').'-'.$booking->id.'-'.str()->upper(str()->random(3)),
            'method' => 'bank_transfer',
            'sender_name' => $request->sender_name,
            'sender_bank' => $request->sender_bank,
            'sender_account' => $request->sender_account,
            'transfer_at' => $request->transfer_at,
            'amount' => $booking->outstanding_amount,
            'proof_file' => $path,
            'status' => 'waiting_confirmation',
        ]);
        $booking->update(['payment_status' => 'waiting_confirmation']);

        return back()->with('success', 'Bukti pembayaran terkirim dan sedang diverifikasi.');
    }
}
