<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingAvailabilityService
{
    public function check(Product $product, string|Carbon $start, string|Carbon $end, int $quantity = 1, ?int $ignoreBookingId = null): array
    {
        $startAt = $start instanceof Carbon ? $start->copy() : Carbon::parse($start);
        $endAt = $end instanceof Carbon ? $end->copy() : Carbon::parse($end);

        if ($endAt->lte($startAt)) {
            throw ValidationException::withMessages(['end_at' => 'Tanggal selesai harus setelah tanggal mulai.']);
        }

        if ($product->status !== 'active') {
            return $this->result(false, 'Barang sedang tidak tersedia untuk disewa.');
        }

        if ($product->partner->verification_status !== 'verified') {
            return $this->result(false, 'Barang sedang tidak tersedia untuk disewa.');
        }

        $isBlackout = $product->blackoutDates()
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();

        if ($isBlackout) {
            return $this->result(false, 'Jumlah unit yang dipilih tidak tersedia pada jadwal tersebut. Silakan kurangi jumlah unit atau pilih tanggal lain.');
        }

        $used = $product->bookingItems()
            ->whereHas('booking', function ($query) use ($ignoreBookingId) {
                $query->whereIn('status', Booking::ACTIVE_RENTAL_STATUSES);
                if ($ignoreBookingId) {
                    $query->whereKeyNot($ignoreBookingId);
                }
            })
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->sum('quantity');

        $available = max(0, $product->stock_total - $used);

        return $available >= $quantity
            ? $this->result(true, 'Barang tersedia untuk tanggal yang dipilih.')
            : $this->result(false, 'Jumlah unit yang dipilih tidak tersedia pada jadwal tersebut. Silakan kurangi jumlah unit atau pilih tanggal lain.');
    }

    private function result(bool $available, string $message): array
    {
        return ['available' => $available, 'message' => $message];
    }
}
