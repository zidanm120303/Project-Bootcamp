<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['booking.customer', 'booking.partner'])
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.payments', compact('payments'));
    }

    public function update(Payment $payment)
    {
        $data = request()->validate(['status' => ['required', 'in:paid,rejected'], 'notes' => ['nullable', 'string', 'max:1000']]);
        $payment->update($data + [
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);
        $payment->booking->update([
            'payment_status' => $data['status'],
            'status' => $data['status'] === 'paid' ? 'paid' : 'waiting_payment',
        ]);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }
}
