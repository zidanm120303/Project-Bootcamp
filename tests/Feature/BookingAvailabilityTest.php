<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\PartnerProfile;
use App\Models\Product;
use App\Models\User;
use App\Services\BookingAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $owner = User::factory()->create(['role' => 'mitra']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $partner = PartnerProfile::create([
            'user_id' => $owner->id, 'business_name' => 'Mitra Test', 'owner_name' => 'Owner',
            'phone' => '0800', 'address' => 'Alamat', 'city' => 'Jakarta', 'province' => 'DKI',
            'verification_status' => 'verified',
        ]);
        $category = Category::create(['name' => 'Kamera', 'slug' => 'kamera', 'status' => 'active']);
        $this->product = Product::create([
            'partner_id' => $partner->id, 'category_id' => $category->id, 'name' => 'Kamera Test',
            'slug' => 'kamera-test', 'product_type' => 'rental', 'description' => 'Produk kamera untuk pengujian.',
            'price' => 100000, 'price_unit' => 'day', 'stock_total' => 3,
            'location_city' => 'Jakarta', 'status' => 'active',
        ]);
    }

    public function test_overlapping_booking_allows_quantity_within_remaining_stock(): void
    {
        $this->blockingBooking(2);
        $result = app(BookingAvailabilityService::class)->check($this->product, '2026-07-11', '2026-07-13', 1);
        $this->assertTrue($result['available']);
        $this->assertSame('Barang tersedia untuk tanggal yang dipilih.', $result['message']);
    }

    public function test_overlapping_booking_rejects_quantity_above_remaining_stock(): void
    {
        $this->blockingBooking(2);
        $result = app(BookingAvailabilityService::class)->check($this->product, '2026-07-11', '2026-07-13', 2);
        $this->assertFalse($result['available']);
        $this->assertSame(
            'Jumlah unit yang dipilih tidak tersedia pada jadwal tersebut. Silakan kurangi jumlah unit atau pilih tanggal lain.',
            $result['message']
        );
    }

    public function test_blackout_date_is_rejected(): void
    {
        $this->product->blackoutDates()->create([
            'start_at' => '2026-07-15 00:00:00', 'end_at' => '2026-07-16 00:00:00', 'reason' => 'Maintenance',
        ]);
        $result = app(BookingAvailabilityService::class)->check($this->product, '2026-07-14', '2026-07-17', 1);
        $this->assertFalse($result['available']);
    }

    public function test_only_active_rental_statuses_reduce_availability(): void
    {
        $booking = $this->blockingBooking(2);
        $availability = app(BookingAvailabilityService::class);

        foreach (Booking::ACTIVE_RENTAL_STATUSES as $status) {
            $booking->update(['status' => $status]);
            $this->assertFalse(
                $availability->check($this->product, '2026-07-11', '2026-07-13', 2)['available'],
                "Status {$status} harus mengurangi ketersediaan."
            );
        }

        foreach (['returned', 'completed', 'cancelled'] as $status) {
            $booking->update(['status' => $status]);
            $this->assertTrue(
                $availability->check($this->product, '2026-07-11', '2026-07-13', 2)['available'],
                "Status {$status} tidak boleh mengurangi ketersediaan."
            );
        }
    }

    public function test_calendar_and_suggestions_follow_real_remaining_stock(): void
    {
        $this->blockingBooking(2);
        $availability = app(BookingAvailabilityService::class);
        $calendar = $availability->calendar($this->product, '2026-07-10', 4, 2);

        $this->assertSame('unavailable', $calendar[0]['status']);
        $this->assertSame(1, $calendar[0]['available_units']);
        $this->assertSame('unavailable', $calendar[1]['status']);
        $this->assertSame('available', $calendar[2]['status']);
        $this->assertSame(3, $calendar[2]['available_units']);

        $suggestions = $availability->suggestions(
            $this->product,
            '2026-07-11',
            '2026-07-13',
            2
        );

        $this->assertNotEmpty($suggestions);
        $this->assertSame('2026-07-12', $suggestions[0]['start_at']);
        $this->assertSame('2026-07-14', $suggestions[0]['end_at']);

        $this->postJson(route('products.availability', $this->product), [
            'start_at' => '2026-07-11',
            'end_at' => '2026-07-13',
            'quantity' => 2,
        ])
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('available_units', 1)
            ->assertJsonPath('suggestions.0.start_at', '2026-07-12');
    }

    public function test_daily_capacity_rule_changes_calendar_fill_and_availability(): void
    {
        $this->product->availabilities()->create([
            'day_of_week' => 6,
            'start_time' => '08:00',
            'end_time' => '17:00',
            'capacity' => 1,
            'is_available' => true,
        ]);

        $availability = app(BookingAvailabilityService::class);

        $this->assertFalse(
            $availability->check($this->product, '2026-07-11', '2026-07-12', 2)['available']
        );
        $this->assertSame(
            'limited',
            $availability->calendar($this->product, '2026-07-11', 1, 1)[0]['status']
        );
    }

    private function blockingBooking(int $quantity): Booking
    {
        $booking = Booking::create([
            'booking_code' => 'TEST-'.uniqid(), 'customer_id' => $this->customer->id,
            'partner_id' => $this->product->partner_id, 'booking_type' => 'rental',
            'start_at' => '2026-07-10', 'end_at' => '2026-07-12', 'subtotal_amount' => 200000,
            'platform_fee' => 0, 'total_amount' => 200000, 'status' => 'confirmed',
        ]);
        $booking->items()->create([
            'product_id' => $this->product->id, 'quantity' => $quantity, 'price_per_unit' => 100000,
            'price_unit' => 'day', 'rental_days' => 2, 'start_at' => '2026-07-10',
            'end_at' => '2026-07-12', 'subtotal' => 200000,
        ]);

        return $booking;
    }
}
