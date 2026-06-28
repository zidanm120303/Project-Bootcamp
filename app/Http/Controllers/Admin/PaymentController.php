<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['booking.customer', 'booking.partner', 'verifier'])
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(request('q'), fn ($query, $term) => $query->where(function ($subQuery) use ($term) {
                $subQuery->where('payment_code', 'like', "%{$term}%")
                    ->orWhereHas('booking', fn ($booking) => $booking->where('booking_code', 'like', "%{$term}%"));
            }))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.payments', compact('payments'));
    }

    public function show(Payment $payment)
    {
        return view('admin.payment-show', [
            'payment' => $payment->load([
                'booking.customer', 'booking.partner', 'booking.items.product.primaryImage', 'verifier',
            ]),
        ]);
    }

    public function update(Payment $payment)
    {
        abort_unless($payment->status === 'waiting_confirmation', 422, 'Pembayaran ini sudah selesai ditinjau.');
        $data = request()->validate([
            'status' => ['required', 'in:paid,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
        ]);
        $payment->update($data + [
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);
        $paidAmount = (float) $payment->booking->payments()->where('status', 'paid')->sum('amount');
        $bookingUpdate = [
            'payment_status' => $data['status'] === 'paid' && $paidAmount >= (float) $payment->booking->total_amount
                ? 'paid'
                : $data['status'],
        ];
        if ($bookingUpdate['payment_status'] === 'paid' && (float) $payment->booking->deposit_amount > 0) {
            $bookingUpdate['deposit_status'] = 'held';
        }
        $payment->booking->update($bookingUpdate);

        return redirect()->route('admin.payments.show', $payment)->with('success', 'Pembayaran berhasil diverifikasi.');
    }
}
