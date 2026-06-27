<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use App\Services\TrustedPartnerScoreService;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = PartnerProfile::with(['user', 'documents'])
            ->when(request('status'), fn ($q, $status) => $q->where('verification_status', $status))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.partners', compact('partners'));
    }

    public function update(PartnerProfile $partner, TrustedPartnerScoreService $score)
    {
        $data = request()->validate([
            'verification_status' => ['required', 'in:verified,rejected,suspended,pending'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $partner->update([
            'verification_status' => $data['verification_status'],
            'verified_at' => $data['verification_status'] === 'verified' ? now() : null,
        ]);
        $partner->documents()->where('status', 'pending')->update([
            'status' => $data['verification_status'] === 'verified' ? 'approved' : 'rejected',
            'admin_notes' => $data['admin_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        $score->recalculate($partner);

        return back()->with('success', 'Status verifikasi mitra diperbarui.');
    }
}
