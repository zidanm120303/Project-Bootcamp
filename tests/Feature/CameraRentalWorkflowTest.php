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

class CameraRentalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_camera_rental_workflow_across_admin_partner_and_customer(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'mitra']);
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '081234567890']);
        $partner = PartnerProfile::create([
            'user_id' => $owner->id,
            'business_name' => 'Rental Kamera Test',
            'business_type' => 'Rental Kamera',
            'owner_name' => $owner->name,
            'phone' => '081200001111',
            'business_email' => 'rental@example.test',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => $owner->name,
            'address' => 'Jl. Kamera No. 1',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'operational_hours' => '08.00-20.00 WIB',
            'pickup_note' => 'Tunjukkan identitas asli.',
            'verification_status' => 'pending',
        ]);

        foreach (['ktp', 'nib', 'rekening', 'foto_usaha'] as $type) {
            $path = "partner-documents/{$type}.pdf";
            Storage::put($path, "dokumen {$type}");
            $document = $partner->documents()->create([
                'document_type' => $type,
                'document_name' => strtoupper($type).' Rental Kamera Test',
                'document_number' => strtoupper($type).'-001',
                'file_path' => $path,
                'is_required' => true,
            ]);
            $this->actingAs($admin)
                ->patch(route('admin.partners.documents.update', [$partner, $document]), [
                    'status' => 'approved',
                    'admin_notes' => 'Data sesuai.',
                ])
                ->assertRedirect();
        }

        $this->actingAs($admin)
            ->get(route('admin.partners.show', $partner))
            ->assertOk()
            ->assertSee('Semua dokumen wajib disetujui');
        $this->actingAs($admin)
            ->patch(route('admin.partners.update', $partner), ['verification_status' => 'verified'])
            ->assertRedirect();
        $this->assertSame('verified', $partner->fresh()->verification_status);
        $this->actingAs($owner)->get(route('partners.documents.show', $document))->assertOk();
        $this->actingAs($admin)->get(route('partners.documents.show', $document))->assertOk();
        $this->actingAs($customer)->get(route('partners.documents.show', $document))->assertForbidden();

        $category = Category::create(['name' => 'Mirrorless', 'slug' => 'mirrorless', 'status' => 'active']);
        $product = Product::create([
            'partner_id' => $partner->id,
            'category_id' => $category->id,
            'name' => 'Sony A7 IV Test Kit',
            'brand' => 'Sony',
            'model' => 'A7 IV',
            'camera_type' => 'Mirrorless',
            'sensor_type' => 'Full Frame CMOS',
            'resolution_mp' => 33,
            'video_resolution' => '4K 60fps',
            'lens_mount' => 'Sony E-Mount',
            'condition_label' => 'Sangat Baik',
            'included_accessories' => 'Body, lensa, baterai, charger, tas.',
            'rental_terms' => 'Identitas asli wajib ditunjukkan.',
            'slug' => 'sony-a7-iv-test-kit',
            'product_type' => 'rental',
            'description' => 'Kamera pengujian alur rental lengkap.',
            'price' => 100000,
            'security_deposit' => 50000,
            'replacement_value' => 30000000,
            'price_unit' => 'day',
            'stock_total' => 2,
            'min_rent_duration' => 1,
            'location_city' => 'Jakarta',
            'status' => 'active',
        ]);
        $product->units()->create([
            'unit_code' => 'CAM-001-001',
            'serial_number' => 'SONY-TEST-001',
            'condition_status' => 'good',
            'availability_status' => 'available',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Full Frame CMOS')
            ->assertSee('Body, lensa, baterai, charger, tas.');
        $this->actingAs($admin)->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee('SONY-TEST-001');

        $start = today()->addDay();
        $end = $start->copy()->addDays(2);
        $this->actingAs($customer)->post(route('customer.bookings.store'), [
            'product_id' => $product->id,
            'start_at' => $start->toDateString(),
            'end_at' => $end->toDateString(),
            'quantity' => 1,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'customer_address' => 'Jl. Customer No. 10',
            'identity_number' => '3174000000000001',
            'identity_file' => UploadedFile::fake()->create('identitas.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $booking = $customer->bookings()->firstOrFail();
        $this->assertSame('pending', $booking->status);
        $this->assertSame('50000.00', $booking->deposit_amount);
        $this->assertSame('260000.00', $booking->total_amount);

        $this->actingAs($owner)
            ->patch(route('mitra.bookings.update', $booking), ['status' => 'confirmed'])
            ->assertRedirect();
        $this->assertNotNull($booking->fresh()->confirmed_at);

        $this->actingAs($customer)
            ->post(route('customer.payments.store', $booking), [
                'sender_name' => $customer->name,
                'sender_bank' => 'BCA',
                'sender_account' => '0987654321',
                'transfer_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'proof_file' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();
        $payment = $booking->payments()->firstOrFail();
        $this->assertSame('waiting_confirmation', $payment->status);
        $this->actingAs($customer)->get(route('payments.proof', $payment))->assertOk();
        $this->actingAs($owner)->get(route('payments.proof', $payment))->assertOk();
        $this->actingAs($admin)->get(route('payments.proof', $payment))->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.payments.show', $payment))
            ->assertOk()
            ->assertSee($booking->booking_code);
        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $payment), ['status' => 'paid', 'notes' => 'Transfer sesuai.'])
            ->assertRedirect(route('admin.payments.show', $payment));
        $this->assertSame('paid', $booking->fresh()->payment_status);
        $this->assertSame('held', $booking->fresh()->deposit_status);

        foreach (['ready_pickup', 'ongoing'] as $status) {
            $this->actingAs($owner)
                ->patch(route('mitra.bookings.update', $booking), ['status' => $status])
                ->assertRedirect();
        }
        $this->actingAs($owner)
            ->patch(route('mitra.bookings.update', $booking), [
                'status' => 'returned',
                'return_condition' => 'Kamera lengkap dan berfungsi normal.',
                'late_fee' => 50000,
                'damage_fee' => 0,
            ])
            ->assertRedirect();
        $booking->refresh();
        $this->assertSame('returned', $booking->status);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertSame(50000.0, $booking->outstanding_amount);

        $this->actingAs($customer)
            ->post(route('customer.payments.store', $booking), [
                'sender_name' => $customer->name,
                'sender_bank' => 'BCA',
                'sender_account' => '0987654321',
                'transfer_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'proof_file' => UploadedFile::fake()->create('pelunasan.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();
        $additionalPayment = $booking->payments()->latest('id')->firstOrFail();
        $this->assertSame('50000.00', $additionalPayment->amount);

        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $additionalPayment), ['status' => 'paid'])
            ->assertRedirect(route('admin.payments.show', $additionalPayment));
        $this->actingAs($owner)
            ->patch(route('mitra.bookings.update', $booking), ['status' => 'completed'])
            ->assertRedirect();
        $this->assertSame('pending_refund', $booking->fresh()->deposit_status);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.deposit', $booking))
            ->assertRedirect();
        $booking->refresh();
        $this->assertSame('completed', $booking->status);
        $this->assertSame('refunded', $booking->deposit_status);
        $this->assertNotNull($booking->deposit_refunded_at);
    }

    public function test_unverified_partner_cannot_submit_or_publish_product(): void
    {
        $owner = User::factory()->create(['role' => 'mitra']);
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = PartnerProfile::create([
            'user_id' => $owner->id,
            'business_name' => 'Mitra Belum Terverifikasi',
            'owner_name' => $owner->name,
            'phone' => '081200001111',
            'address' => 'Jl. Kamera',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'verification_status' => 'pending',
        ]);
        $category = Category::create(['name' => 'Mirrorless', 'slug' => 'mirrorless', 'status' => 'active']);
        $productData = [
            'name' => 'Sony A7 IV Pending',
            'category_id' => $category->id,
            'product_type' => 'rental',
            'brand' => 'Sony',
            'model' => 'A7 IV',
            'camera_type' => 'Mirrorless',
            'condition_label' => 'Sangat Baik',
            'included_accessories' => 'Body, baterai, charger.',
            'rental_terms' => 'Identitas wajib.',
            'description' => 'Produk kamera milik mitra yang belum terverifikasi.',
            'price' => 400000,
            'security_deposit' => 150000,
            'price_unit' => 'day',
            'stock_total' => 1,
            'min_rent_duration' => 1,
            'location_city' => 'Jakarta',
            'submit_review' => 1,
        ];

        $this->actingAs($owner)
            ->post(route('mitra.products.store'), $productData)
            ->assertStatus(422);
        $this->assertDatabaseCount('products', 0);

        $product = Product::create([
            'partner_id' => $partner->id,
            'category_id' => $category->id,
            'name' => 'Kamera Draf',
            'slug' => 'kamera-draf',
            'product_type' => 'rental',
            'description' => 'Kamera belum dapat dipublikasikan.',
            'price' => 100000,
            'price_unit' => 'day',
            'stock_total' => 1,
            'location_city' => 'Jakarta',
            'status' => 'pending_review',
        ]);
        $this->actingAs($admin)
            ->patch(route('admin.products.update', $product), ['status' => 'active'])
            ->assertStatus(422);
        $this->get(route('products.show', $product))->assertNotFound();

        $partner->update(['verification_status' => 'verified']);
        $productData['security_deposit'] = 450000;
        $this->actingAs($owner)
            ->post(route('mitra.products.store'), $productData)
            ->assertSessionHasErrors('security_deposit');
    }

    public function test_seeded_catalog_profile_order_and_review_cards_render_successfully(): void
    {
        Storage::fake('local');
        $this->seed();
        $admin = User::where('email', 'admin@rentra.test')->firstOrFail();
        $owner = User::where('email', 'mitra@rentra.test')->firstOrFail();
        $customer = User::where('email', 'customer@rentra.test')->firstOrFail();

        $this->get(route('catalog'))
            ->assertOk()
            ->assertSee('RentalPro')
            ->assertSee('Sony A7 IV Full Frame Creator Kit');
        $this->actingAs($customer)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dokumen identitas')
            ->assertSee('Kelengkapan profil');
        $this->actingAs($customer)->get(route('customer.bookings.index'))
            ->assertOk()
            ->assertSee('Semua pesanan kamera Anda tersimpan di sini.');
        $this->actingAs($owner)->get(route('mitra.products.index'))
            ->assertOk()
            ->assertSee('Kamera & Unit');
        $this->actingAs($admin)->get(route('admin.partners.index'))
            ->assertOk()
            ->assertSee('Kelengkapan dokumen wajib');
        $this->actingAs($admin)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Nominal transfer');
    }
}
