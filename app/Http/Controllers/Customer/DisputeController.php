<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class DisputeController extends Controller
{
    public function store(Booking $booking)
    {
        $this->authorize('view', $booking);
        $data = request()->validate([
            'issue_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'evidence_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        if (request()->hasFile('evidence_file')) {
            $data['evidence_file'] = request()->file('evidence_file')->store('dispute-evidence', 'public');
        }
        $booking->disputes()->create($data + ['reporter_id' => auth()->id()]);
        $booking->update(['status' => 'disputed']);

        return back()->with('success', 'Komplain diterima. Tim kami akan meninjaunya.');
    }
}
