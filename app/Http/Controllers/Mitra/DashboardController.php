<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;
use App\Services\BookingAvailabilityService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(BookingAvailabilityService $availability)
    {
        $partner = auth()->user()->partnerProfile;
        $bookings = Booking::where('partner_id', $partner->id);
        $activeProducts = Product::where('partner_id', $partner->id)->where('status', 'active')->get();
        $monthStart = today()->startOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $calendarDays = $calendarStart->diffInDays($calendarEnd) + 1;
        $productCalendars = $activeProducts->map(
            fn (Product $product) => $availability->calendar($product, $calendarStart, $calendarDays)
        );

        $availabilityCalendar = collect(range(0, $calendarDays - 1))->map(function (int $index) use (
            $calendarStart,
            $monthStart,
            $productCalendars
        ) {
            $date = $calendarStart->copy()->addDays($index);
            $states = $productCalendars->map(fn (array $calendar) => $calendar[$index]);
            $capacity = $states->sum('capacity');
            $availableUnits = $states->sum('available_units');
            $status = match (true) {
                $date->lt(today()) => 'past',
                $states->isEmpty() || $availableUnits === 0 => 'unavailable',
                $states->contains(fn ($state) => in_array($state['status'], ['limited', 'unavailable'], true)) => 'limited',
                default => 'available',
            };

            return [
                'date' => $date->toDateString(),
                'date_label' => $date->format('j'),
                'is_today' => $date->isToday(),
                'is_current_month' => $date->month === $monthStart->month,
                'capacity' => $capacity,
                'available_units' => $availableUnits,
                'status' => $status,
            ];
        });

        return view('mitra.dashboard', [
            'partner' => $partner->load('documents'),
            'recentBookings' => (clone $bookings)->with(['customer', 'items.product'])->latest()->limit(6)->get(),
            'lowStockProducts' => Product::where('partner_id', $partner->id)->orderBy('stock_total')->limit(5)->get(),
            'availabilityCalendar' => $availabilityCalendar,
            'availabilityMonth' => $monthStart->translatedFormat('F Y'),
            'stats' => [
                'products' => Product::where('partner_id', $partner->id)->where('status', 'active')->count(),
                'today' => (clone $bookings)->whereDate('created_at', today())->count(),
                'revenue' => (clone $bookings)->where('payment_status', 'paid')->sum('subtotal_amount'),
                'pending' => (clone $bookings)->where('status', 'pending')->count(),
            ],
        ]);
    }
}
