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
            ->withCount(['documents', 'products', 'bookings'])
            ->when(request('status'), fn ($q, $status) => $q->where('verification_status', $status))
            ->when(request('q'), fn ($query, $term) => $query->where(function ($subQuery) use ($term) {
                $subQuery->where('business_name', 'like', "%{$term}%")
                    ->orWhere('owner_name', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($user) => $user->where('email', 'like', "%{$term}%"));
            }))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.partners', compact('partners'));
    }

    public function show(PartnerProfile $partner)
    {
        return view('admin.partner-show', [
            'partner' => $partner->load([
                'user', 'documents.reviewer', 'products.category', 'bookings',
            ])->loadCount(['products', 'bookings']),
            'requiredDocuments' => ['ktp', 'nib', 'rekening', 'foto_usaha'],
        ]);
    }

    public function update(PartnerProfile $partner, TrustedPartnerScoreService $score)
    {
        $data = request()->validate([
            'verification_status' => ['required', 'in:verified,rejected,suspended,pending'],
            'admin_notes' => ['required_if:verification_status,rejected,suspended', 'nullable', 'string', 'max:2000'],
        ]);

        if ($data['verification_status'] === 'verified') {
            $latestStatuses = $partner->documents()->latest('id')->get()
                ->unique('document_type')
                ->pluck('status', 'document_type');
            abort_unless(
                collect(['ktp', 'nib', 'rekening', 'foto_usaha'])->every(fn ($type) => $latestStatuses->get($type) === 'approved'),
                422,
                'KTP, NIB, rekening, dan foto usaha harus disetujui sebelum mitra diverifikasi.'
            );
        }

        $partner->update([
            'verification_status' => $data['verification_status'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'verified_at' => $data['verification_status'] === 'verified' ? now() : null,
        ]);
        $score->recalculate($partner);

        return back()->with('success', 'Status verifikasi mitra diperbarui.');
    }

    public function updateDocument(PartnerProfile $partner, \App\Models\PartnerDocument $document)
    {
        abort_unless($document->partner_id === $partner->id, 404);
        $data = request()->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_notes' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
        ]);
        $document->update($data + [
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Dokumen mitra berhasil ditinjau.');
    }
}
