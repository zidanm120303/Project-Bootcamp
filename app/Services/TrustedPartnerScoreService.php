<?php

namespace App\Services;

use App\Models\PartnerProfile;
use App\Models\SystemSetting;

class TrustedPartnerScoreService
{
    public function recalculate(PartnerProfile $partner): int
    {
        $completed = $partner->bookings()->where('status', 'completed')->count();
        $total = max(1, $partner->bookings()->count());
        $cancelRate = $partner->bookings()->where('status', 'cancelled')->count() / $total;
        $rating = (float) $partner->reviews()->avg('rating');

        $score = 0;
        $score += $partner->verification_status === 'verified' ? 25 : 0;
        $score += $rating >= 4.5 ? 20 : 0;
        $score += $completed >= 20 ? 20 : min(19, $completed);
        $score += $cancelRate <= .1 ? 15 : 5;
        $score += 10;
        $score += $partner->bookings()->where('status', 'disputed')->doesntExist() ? 10 : 0;

        $minimum = (int) SystemSetting::valueFor('trusted_min_score', 85);
        $partner->update(['trusted_score' => $score, 'is_trusted' => $score >= $minimum]);

        return $score;
    }
}
