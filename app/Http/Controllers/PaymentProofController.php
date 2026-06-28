<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    public function __invoke(Payment $payment)
    {
        $user = auth()->user();
        $payment->loadMissing('booking');
        $allowed = $user->role === 'admin'
            || ($user->role === 'customer' && $payment->booking->customer_id === $user->id)
            || ($user->role === 'mitra' && $payment->booking->partner_id === $user->partnerProfile?->id);

        abort_unless($allowed, 403);
        abort_unless($payment->proof_file && Storage::exists($payment->proof_file), 404);

        $extension = pathinfo($payment->proof_file, PATHINFO_EXTENSION);

        return Storage::download(
            $payment->proof_file,
            'bukti-'.$payment->payment_code.'.'.$extension
        );
    }
}
