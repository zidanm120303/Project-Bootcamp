<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PartnerProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RentalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_book_multiple_units_with_private_identity_and_unique_code(): void
    {
        Storage::fake('local');
        [$customer, $product] = $this->rentalFixture();
        $start = today()->addDay();
        $end = $start->copy()->addDays(2);

        $response = $this->actingAs($customer)->post(route('customer.bookings.store'), [
            'product_id' => $product->id,
            'start_at' => $start->toDateString(),
            'end_at' => $end->toDateString(),
            'quantity' => 3,
            'customer_name' => 'Customer Penyewa',
            'customer_phone' => '081234567890',
            'customer_email' => 'penyewa@example.test',
            'customer_address' => 'Jl. Pengujian No. 10',
            'identity_number' => '3174000012345678',
            'identity_file' => UploadedFile::fake()->create('identitas.pdf', 100, 'application/pdf'),
            'customer_notes' => 'Akan diambil pagi hari.',
        ]);

        $booking = $customer->bookings()->with('items')->firstOrFail();
        $response->assertRedirect(route('customer.bookings.show', $booking));
        $this->assertMatchesRegularExpression('/^SW-\d{8}-0001$/', $booking->booking_code);
        $this->assertSame('store_pickup', $booking->pickup_method);
        $this->assertSame(3, $booking->items->first()->quantity);
        $this->assertSame(2, $booking->items->first()->rental_days);
        $this->assertSame('600000.00', $booking->items->first()->subtotal);
        Storage::disk('local')->assertExists($booking->identity_file);
    }

    public function test_checkout_rejects_quantity_that_is_not_available_without_revealing_stock_count(): void
    {
        Storage::fake('local');
        [$customer, $product] = $this->rentalFixture();
        $start = today()->addDay();
        $end = $start->copy()->addDays(2);
        $message = 'Jumlah unit yang dipilih tidak tersedia pada jadwal tersebut. Silakan kurangi jumlah unit atau pilih tanggal lain.';

        $this->actingAs($customer)
            ->from(route('products.show', $product))
            ->get(route('customer.checkout', [
                'product' => $product,
                'start_at' => $start->toDateString(),
                'end_at' => $end->toDateString(),
                'quantity' => 99,
            ]))
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors(['quantity' => $message]);

        $response = $this->actingAs($customer)->from(route('customer.checkout', [
            'product' => $product,
            'start_at' => $start->toDateString(),
            'end_at' => $end->toDateString(),
            'quantity' => 99,
        ]))->post(route('customer.bookings.store'), [
            'product_id' => $product->id,
            'start_at' => $start->toDateString(),
            'end_at' => $end->toDateString(),
            'quantity' => 99,
            'customer_name' => 'Customer Penyewa',
            'customer_phone' => '081234567890',
            'customer_email' => 'penyewa@example.test',
            'customer_address' => 'Jl. Pengujian No. 10',
            'identity_number' => '3174000012345678',
            'identity_file' => UploadedFile::fake()->create('identitas.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors([
            'quantity' => $message,
        ]);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_checkout_can_use_private_identity_from_customer_profile(): void
    {
        Storage::fake('local');
        [$customer, $product] = $this->rentalFixture();
        Storage::put('user-identities/profile.pdf', 'identitas profil');
        $customer->update([
            'identity_type' => 'ktp',
            'identity_number' => '3174000012345678',
            'identity_file' => 'user-identities/profile.pdf',
        ]);
        $start = today()->addDay();

        $this->actingAs($customer)->post(route('customer.bookings.store'), [
            'product_id' => $product->id,
            'start_at' => $start->toDateString(),
            'end_at' => $start->copy()->addDays(2)->toDateString(),
            'quantity' => 1,
            'customer_name' => $customer->name,
            'customer_phone' => '081234567890',
            'customer_email' => $customer->email,
            'customer_address' => 'Jl. Pengujian No. 10',
            'identity_number' => $customer->identity_number,
        ])->assertRedirect();

        $booking = $customer->bookings()->firstOrFail();
        $this->assertNotSame($customer->identity_file, $booking->identity_file);
        Storage::assertExists($booking->identity_file);
    }

    private function rentalFixture(): array
    {
        $owner = User::factory()->create(['role' => 'mitra']);
        $customer = User::factory()->create(['role' => 'customer']);
        $partner = PartnerProfile::create([
            'user_id' => $owner->id,
            'business_name' => 'Toko Rental Test',
            'owner_name' => 'Owner Test',
            'phone' => '081100001111',
            'operational_hours' => 'Senin–Sabtu, 08.00–20.00',
            'pickup_note' => 'Bawa identitas asli.',
            'address' => 'Jl. Mitra No. 1',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'verification_status' => 'verified',
        ]);
        $category = Category::create(['name' => 'Kamera', 'slug' => 'kamera', 'status' => 'active']);
        $product = Product::create([
            'partner_id' => $partner->id,
            'category_id' => $category->id,
            'name' => 'Kamera Rental',
            'slug' => 'kamera-rental',
            'product_type' => 'rental',
            'description' => 'Kamera rental untuk kebutuhan pengujian sistem.',
            'price' => 100000,
            'price_unit' => 'day',
            'stock_total' => 5,
            'location_city' => 'Jakarta',
            'status' => 'active',
        ]);

        return [$customer, $product];
    }
}
