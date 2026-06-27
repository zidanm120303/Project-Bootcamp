<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::with(['reporter', 'booking.partner', 'booking.items.product'])
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.disputes', compact('disputes'));
    }

    public function update(Dispute $dispute)
    {
        $data = request()->validate([
            'status' => ['required', 'in:reviewed,waiting_partner_response,waiting_customer_response,resolved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1500'],
        ]);
        $dispute->update($data + ['resolved_at' => in_array($data['status'], ['resolved', 'rejected']) ? now() : null]);
        if ($data['status'] === 'resolved') {
            $dispute->booking->update(['status' => 'completed']);
        }

        return back()->with('success', 'Komplain berhasil diperbarui.');
    }
}
