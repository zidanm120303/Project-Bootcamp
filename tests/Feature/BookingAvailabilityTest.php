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

    private function blockingBooking(int $quantity): Booking
    {
        $booking = Booking::create([
            'booking_code' => 'TEST-'.uniqid(), 'customer_id' => $this->customer->id,
            'partner_id' => $this->product->partner_id, 'booking_type' => 'rental',
            'start_at' => '2026-07-10', 'end_at' => '2026-07-12', 'subtotal_amount' => 200000,
            'platform_fee' => 10000, 'total_amount' => 210000, 'status' => 'confirmed',
        ]);
        $booking->items()->create([
            'product_id' => $this->product->id, 'quantity' => $quantity, 'price_per_unit' => 100000,
            'price_unit' => 'day', 'rental_days' => 2, 'start_at' => '2026-07-10',
            'end_at' => '2026-07-12', 'subtotal' => 200000,
        ]);

        return $booking;
    }
}
