<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;

class BookingPriceService
{
    public function calculate(Product $product, string|Carbon $start, string|Carbon $end, int $qty): array
    {
        $startAt = $start instanceof Carbon ? $start : Carbon::parse($start);
        $endAt = $end instanceof Carbon ? $end : Carbon::parse($end);

        $duration = match ($product->price_unit) {
            'hour' => max(1, $startAt->diffInHours($endAt)),
            'week' => max(1, (int) ceil($startAt->diffInDays($endAt) / 7)),
            'month' => max(1, (int) ceil($startAt->diffInDays($endAt) / 30)),
            'service', 'item' => 1,
            default => max(1, $startAt->copy()->startOfDay()->diffInDays($endAt->copy()->startOfDay())),
        };

        $subtotal = (float) $product->price * $duration * $qty;
        $fee = round($subtotal * ((float) \App\Models\SystemSetting::valueFor('platform_fee_percent', 5) / 100));

        return [
            'duration' => $duration,
            'subtotal' => $subtotal,
            'platform_fee' => $fee,
            'total' => $subtotal + $fee,
        ];
    }
}
