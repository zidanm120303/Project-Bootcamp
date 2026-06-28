<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingAvailabilityService
{
    public function check(
        Product $product,
        string|Carbon $start,
        string|Carbon $end,
        int $quantity = 1,
        ?int $ignoreBookingId = null
    ): array {
        $startAt = $this->date($start);
        $endAt = $this->date($end);

        if ($endAt->lte($startAt)) {
            throw ValidationException::withMessages(['end_at' => 'Tanggal selesai harus setelah tanggal mulai.']);
        }

        if ($product->status !== 'active' || $product->partner->verification_status !== 'verified') {
            return $this->result(false, 'Barang sedang tidak tersedia untuk disewa.', 0);
        }

        $context = $this->context($product, $startAt, $endAt, $ignoreBookingId);
        $range = $this->rangeState($product, $startAt, $endAt, $quantity, $context);

        return $range['available']
            ? $this->result(true, 'Barang tersedia untuk tanggal yang dipilih.', $range['available_units'])
            : $this->result(
                false,
                'Jumlah unit yang dipilih tidak tersedia pada jadwal tersebut. Silakan kurangi jumlah unit atau pilih tanggal lain.',
                $range['available_units']
            );
    }

    public function calendar(Product $product, string|Carbon $start, int $days = 14, int $quantity = 1): array
    {
        $startAt = $this->date($start);
        $endAt = $startAt->copy()->addDays($days);
        $context = $this->context($product, $startAt, $endAt);

        return collect(range(0, $days - 1))->map(function (int $offset) use ($product, $startAt, $quantity, $context) {
            $date = $startAt->copy()->addDays($offset);
            $state = $this->dayState($product, $date, $context);
            $status = match (true) {
                $date->lt(today()) => 'past',
                $state['capacity'] === 0 || $state['available_units'] < $quantity => 'unavailable',
                $state['available_units'] < $product->stock_total => 'limited',
                default => 'available',
            };

            return [
                'date' => $date->toDateString(),
                'day_label' => $date->translatedFormat('D'),
                'date_label' => $date->format('j'),
                'month_label' => $date->translatedFormat('M'),
                'is_today' => $date->isToday(),
                'is_current_month' => $date->month === $startAt->month,
                'capacity' => $state['capacity'],
                'available_units' => $state['available_units'],
                'status' => $status,
            ];
        })->all();
    }

    public function suggestions(
        Product $product,
        string|Carbon $start,
        string|Carbon $end,
        int $quantity = 1,
        int $limit = 3
    ): array {
        $startAt = $this->date($start);
        $endAt = $this->date($end);
        $duration = max(1, $startAt->diffInDays($endAt));
        $searchStart = $startAt->copy()->addDay()->max(today());
        $searchEnd = $searchStart->copy()->addDays(60 + $duration);
        $context = $this->context($product, $searchStart, $searchEnd);
        $suggestions = [];

        for ($offset = 0; $offset < 60 && count($suggestions) < $limit; $offset++) {
            $candidateStart = $searchStart->copy()->addDays($offset);
            $candidateEnd = $candidateStart->copy()->addDays($duration);
            $state = $this->rangeState($product, $candidateStart, $candidateEnd, $quantity, $context);

            if (! $state['available']) {
                continue;
            }

            $suggestions[] = [
                'start_at' => $candidateStart->toDateString(),
                'end_at' => $candidateEnd->toDateString(),
                'start_label' => $candidateStart->translatedFormat('d M'),
                'end_label' => $candidateEnd->translatedFormat('d M Y'),
                'duration' => $duration,
                'available_units' => $state['available_units'],
            ];
        }

        return $suggestions;
    }

    private function rangeState(
        Product $product,
        Carbon $start,
        Carbon $end,
        int $quantity,
        array $context
    ): array {
        $availableUnits = null;

        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $state = $this->dayState($product, $date, $context);
            $availableUnits = $availableUnits === null
                ? $state['available_units']
                : min($availableUnits, $state['available_units']);
        }

        $availableUnits ??= 0;

        return [
            'available' => $availableUnits >= $quantity,
            'available_units' => $availableUnits,
        ];
    }

    private function dayState(Product $product, Carbon $date, array $context): array
    {
        $rule = $context['rules']->get($date->isoWeekday());
        $capacity = $rule
            ? ($rule->is_available ? min($product->stock_total, $rule->capacity) : 0)
            : $product->stock_total;

        $dayEnd = $date->copy()->addDay();
        $isBlackout = $context['blackouts']->contains(
            fn ($blackout) => $blackout->start_at->lt($dayEnd) && $blackout->end_at->gt($date)
        );

        if ($isBlackout) {
            $capacity = 0;
        }

        $used = $context['items']
            ->filter(fn ($item) => $item->start_at->lt($dayEnd) && $item->end_at->gt($date))
            ->sum('quantity');

        return [
            'capacity' => $capacity,
            'used_units' => min($capacity, $used),
            'available_units' => max(0, $capacity - $used),
        ];
    }

    private function context(Product $product, Carbon $start, Carbon $end, ?int $ignoreBookingId = null): array
    {
        $items = $product->bookingItems()
            ->whereHas('booking', function ($query) use ($ignoreBookingId) {
                $query->whereIn('status', Booking::ACTIVE_RENTAL_STATUSES);
                if ($ignoreBookingId) {
                    $query->whereKeyNot($ignoreBookingId);
                }
            })
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->get(['id', 'booking_id', 'quantity', 'start_at', 'end_at']);

        return [
            'rules' => $product->availabilities()->get()->keyBy('day_of_week'),
            'blackouts' => $product->blackoutDates()
                ->where('start_at', '<', $end)
                ->where('end_at', '>', $start)
                ->get(),
            'items' => $items,
        ];
    }

    private function date(string|Carbon $date): Carbon
    {
        return ($date instanceof Carbon ? $date->copy() : Carbon::parse($date))->startOfDay();
    }

    private function result(bool $available, string $message, int $availableUnits): array
    {
        return [
            'available' => $available,
            'message' => $message,
            'available_units' => $availableUnits,
        ];
    }
}
