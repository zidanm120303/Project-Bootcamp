<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;

class BookingPriceService
{
    public function calculate(Product $product, string|Carbon $start, string|Carbon $end, int $quantity): array
    {
        $startAt = $start instanceof Carbon ? $start : Carbon::parse($start);
        $endAt = $end instanceof Carbon ? $end : Carbon::parse($end);

        $rentalDays = match ($product->price_unit) {
            'hour' => max(1, $startAt->diffInHours($endAt)),
            'week' => max(1, (int) ceil($startAt->diffInDays($endAt) / 7)),
            'month' => max(1, (int) ceil($startAt->diffInDays($endAt) / 30)),
            'service', 'item' => 1,
            default => max(1, $startAt->copy()->startOfDay()->diffInDays($endAt->copy()->startOfDay())),
        };

        $subtotal = (float) $product->price * $rentalDays * $quantity;
        $deposit = (float) $product->security_deposit * $quantity;

        return [
            'rental_days' => $rentalDays,
            'subtotal' => $subtotal,
            'deposit' => $deposit,
            'platform_fee' => 0,
            'total' => $subtotal + $deposit,
        ];
    }
}
