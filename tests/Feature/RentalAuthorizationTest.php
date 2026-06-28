<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\PartnerProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RentalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_with_history_can_only_be_deactivated_and_active_product_cannot_be_changed(): void
    {
        [$owner, $customer, $product] = $this->fixture();
        $otherOwner = User::factory()->create(['role' => 'mitra']);
        PartnerProfile::create([
            'user_id' => $otherOwner->id, 'business_name' => 'Mitra Lain', 'owner_name' => 'Pemilik Lain',
            'phone' => '0812', 'operational_hours' => '08.00–17.00', 'pickup_note' => 'Bawa identitas.',
            'address' => 'Alamat', 'city' => 'Bandung', 'province' => 'Jawa Barat',
        ]);

        $this->assertTrue($owner->can('delete', $product));
        $this->assertFalse($otherOwner->can('delete', $product));
        $this->actingAs($otherOwner)
            ->delete(route('mitra.products.destroy', $product))
            ->assertForbidden();
        $this->actingAs($otherOwner)
            ->patch(route('mitra.products.status', $product), ['status' => 'inactive'])
            ->assertForbidden();

        $completed = $this->booking($customer, $product, 'completed');
        $this->assertFalse($owner->fresh()->can('delete', $product));
        $this->assertTrue($owner->can('changeStatus', $product));
        $this->actingAs($owner)
            ->delete(route('mitra.products.destroy', $product))
            ->assertForbidden();

        $completed->update(['status' => 'pending']);
        $this->assertFalse($owner->can('changeStatus', $product));
        $this->actingAs($owner)
            ->patch(route('mitra.products.status', $product), ['status' => 'inactive'])
            ->assertForbidden();
    }

    public function test_identity_document_is_private_to_customer_related_partner_and_admin(): void
    {
        Storage::fake('local');
        [$owner, $customer, $product] = $this->fixture();
        $booking = $this->booking($customer, $product, 'pending');
        $booking->update(['identity_file' => 'customer-identities/identity.pdf']);
        Storage::disk('local')->put($booking->identity_file, 'private identity');

        $admin = User::factory()->create(['role' => 'admin']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $otherOwner = User::factory()->create(['role' => 'mitra']);
        PartnerProfile::create([
            'user_id' => $otherOwner->id, 'business_name' => 'Mitra Lain', 'owner_name' => 'Pemilik Lain',
            'phone' => '0812', 'operational_hours' => '08.00–17.00', 'pickup_note' => 'Bawa identitas.',
            'address' => 'Alamat', 'city' => 'Bandung', 'province' => 'Jawa Barat',
        ]);

        $this->actingAs($customer)->get(route('bookings.identity', $booking))->assertOk();
        $this->actingAs($owner)->get(route('bookings.identity', $booking))->assertOk();
        $this->actingAs($admin)->get(route('bookings.identity', $booking))->assertOk();
        $this->actingAs($otherCustomer)->get(route('bookings.identity', $booking))->assertForbidden();
        $this->actingAs($otherOwner)->get(route('bookings.identity', $booking))->assertForbidden();
        $this->actingAs($otherOwner)->get(route('mitra.bookings.show', $booking))->assertForbidden();
    }

    public function test_rental_pages_render_pickup_information_without_leaking_identity_publicly(): void
    {
        [$owner, $customer, $product] = $this->fixture();
        $booking = $this->booking($customer, $product, 'pending');
        $booking->update([
            'pickup_note' => 'Tunjukkan kode booking dan identitas asli.',
            'identity_file' => 'customer-identities/identity.pdf',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Mitra Test')
            ->assertSee('Alamat')
            ->assertSee('0812')
            ->assertSee('08.00–17.00')
            ->assertDontSee($booking->identity_number);

        $this->actingAs($customer)
            ->get(route('customer.checkout', [
                'product' => $product,
                'start_at' => today()->addDay()->toDateString(),
                'end_at' => today()->addDays(3)->toDateString(),
                'quantity' => 1,
            ]))
            ->assertOk()
            ->assertSee('Barang sewa wajib diambil langsung oleh customer di toko mitra');

        $this->actingAs($customer)->get(route('customer.bookings.index'))
            ->assertOk()
            ->assertSee('Mitra Test')
            ->assertSee('08.00–17.00')
            ->assertSee('Tunjukkan kode booking dan identitas asli.');
        $this->actingAs($customer)->get(route('customer.bookings.show', $booking))
            ->assertOk()
            ->assertSee($booking->identity_number);
        $this->actingAs($customer)->get(route('customer.bookings.invoice', $booking))
            ->assertOk()
            ->assertSee($booking->booking_code);
        $this->actingAs($owner)->get(route('mitra.bookings.show', $booking))
            ->assertOk()
            ->assertSee($booking->identity_number);
        $this->actingAs($admin)->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee($booking->identity_number);
    }

    private function fixture(): array
    {
        $owner = User::factory()->create(['role' => 'mitra']);
        $customer = User::factory()->create(['role' => 'customer']);
        $partner = PartnerProfile::create([
            'user_id' => $owner->id, 'business_name' => 'Mitra Test', 'owner_name' => 'Pemilik',
            'phone' => '0812', 'operational_hours' => '08.00–17.00', 'pickup_note' => 'Bawa identitas.',
            'address' => 'Alamat', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
            'verification_status' => 'verified',
        ]);
        $category = Category::create(['name' => 'Kamera', 'slug' => 'kamera', 'status' => 'active']);
        $product = Product::create([
            'partner_id' => $partner->id, 'category_id' => $category->id, 'name' => 'Kamera',
            'slug' => 'kamera', 'product_type' => 'rental', 'description' => 'Produk pengujian rental.',
            'price' => 100000, 'price_unit' => 'day', 'stock_total' => 2,
            'location_city' => 'Jakarta', 'status' => 'active',
        ]);

        return [$owner, $customer, $product];
    }

    private function booking(User $customer, Product $product, string $status): Booking
    {
        $booking = Booking::create([
            'booking_code' => 'SW-20260628-'.str_pad((string) (Booking::count() + 1), 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id, 'partner_id' => $product->partner_id,
            'booking_type' => 'rental', 'customer_name' => $customer->name,
            'customer_phone' => '08123456789', 'customer_email' => $customer->email,
            'customer_address' => 'Alamat Customer', 'identity_number' => '1234567890',
            'start_at' => today()->addDay(), 'end_at' => today()->addDays(3),
            'subtotal_amount' => 200000, 'platform_fee' => 10000, 'total_amount' => 210000,
            'status' => $status,
        ]);
        $booking->items()->create([
            'product_id' => $product->id, 'quantity' => 1, 'price_per_unit' => 100000,
            'price_unit' => 'day', 'rental_days' => 2, 'start_at' => today()->addDay(),
            'end_at' => today()->addDays(3), 'subtotal' => 200000,
        ]);

        return $booking;
    }
}
