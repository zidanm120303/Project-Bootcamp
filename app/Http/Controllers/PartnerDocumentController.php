<?php

namespace App\Http\Controllers;

use App\Models\PartnerDocument;
use Illuminate\Support\Facades\Storage;

class PartnerDocumentController extends Controller
{
    public function __invoke(PartnerDocument $document)
    {
        $user = auth()->user();
        $allowed = $user->role === 'admin'
            || ($user->role === 'mitra' && $user->partnerProfile?->id === $document->partner_id);

        abort_unless($allowed, 403);
        abort_unless(Storage::exists($document->file_path), 404);

        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);

        return Storage::download(
            $document->file_path,
            str($document->document_type)->slug().'-'.$document->partner_id.'.'.$extension
        );
    }
}
