<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingAvailabilityService
{
    private const BLOCKING_STATUSES = ['confirmed', 'waiting_payment', 'paid', 'prepared', 'ongoing'];

    public function check(Product $product, string|Carbon $start, string|Carbon $end, int $qty = 1, ?int $ignoreBookingId = null): array
    {
        $startAt = $start instanceof Carbon ? $start->copy() : Carbon::parse($start);
        $endAt = $end instanceof Carbon ? $end->copy() : Carbon::parse($end);

        if ($endAt->lte($startAt)) {
            throw ValidationException::withMessages(['end_at' => 'Tanggal selesai harus setelah tanggal mulai.']);
        }

        if ($product->status !== 'active') {
            return $this->result(false, 0, 'Produk sedang tidak aktif.');
        }

        if ($product->partner->verification_status !== 'verified') {
            return $this->result(false, 0, 'Mitra belum terverifikasi.');
        }

        $isBlackout = $product->blackoutDates()
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();

        if ($isBlackout) {
            return $this->result(false, 0, 'Produk tidak tersedia pada sebagian tanggal tersebut.');
        }

        $used = $product->bookingItems()
            ->whereHas('booking', function ($query) use ($ignoreBookingId) {
                $query->whereIn('status', self::BLOCKING_STATUSES);
                if ($ignoreBookingId) {
                    $query->whereKeyNot($ignoreBookingId);
                }
            })
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->sum('qty');

        $available = max(0, $product->stock_total - $used);

        return $available >= $qty
            ? $this->result(true, $available, "Tersedia {$available} unit pada tanggal yang dipilih.")
            : $this->result(false, $available, "Stok hanya tersisa {$available} unit pada tanggal tersebut.");
    }

    private function result(bool $available, int $stock, string $message): array
    {
        return ['available' => $available, 'stock' => $stock, 'message' => $message];
    }
}
