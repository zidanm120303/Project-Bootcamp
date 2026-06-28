<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use RuntimeException;

class BookingCodeService
{
    public function generate(?Carbon $date = null): string
    {
        $date ??= now();
        $prefix = 'SW-'.$date->format('Ymd').'-';

        $lastCode = Booking::query()
            ->where('booking_code', 'like', $prefix.'%')
            ->orderByDesc('booking_code')
            ->lockForUpdate()
            ->value('booking_code');

        $nextSequence = $lastCode ? ((int) substr($lastCode, -4)) + 1 : 1;

        if ($nextSequence > 9999) {
            throw new RuntimeException('Batas kode booking harian telah tercapai.');
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
