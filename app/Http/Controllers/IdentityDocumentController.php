<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Storage;

class IdentityDocumentController extends Controller
{
    public function __invoke(Booking $booking)
    {
        $this->authorize('view', $booking);
        abort_unless($booking->identity_file && Storage::exists($booking->identity_file), 404);

        $extension = pathinfo($booking->identity_file, PATHINFO_EXTENSION);

        return Storage::download(
            $booking->identity_file,
            'identitas-'.$booking->booking_code.'.'.$extension
        );
    }
}
