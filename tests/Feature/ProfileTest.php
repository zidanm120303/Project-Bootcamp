<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'phone' => '081234567890']);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'phone' => '081234567890']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '081234567890',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'profile-updated')
            ->assertRedirect('/profile');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Profil akun berhasil diperbarui.');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_invalid_profile_format_is_returned_to_global_notification_layer(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'phone' => '081234567890']);

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'format-email-salah',
                'phone' => '123',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors(['email', 'phone']);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('window.serverValidationErrors', false)
            ->assertSee('Periksa kembali data');
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'phone' => '081234567890']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'phone' => '081234567890',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_customer_can_store_private_complete_profile_and_identity(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '081234567890']);
        $admin = User::factory()->create(['role' => 'admin']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->patch('/profile', [
            'name' => 'Customer Lengkap',
            'email' => $customer->email,
            'phone' => '081234567890',
            'date_of_birth' => '1998-01-10',
            'gender' => 'female',
            'profession' => 'Fotografer',
            'address' => 'Jl. Kamera No. 10',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '12345',
            'identity_type' => 'ktp',
            'identity_number' => '3174000000000001',
            'identity_file' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            'emergency_contact_name' => 'Kontak Darurat',
            'emergency_contact_phone' => '081200001111',
        ])->assertRedirect('/profile')->assertSessionHasNoErrors();

        $customer->refresh();
        Storage::assertExists($customer->identity_file);
        $this->actingAs($customer)->get(route('profile.identity', $customer))->assertOk();
        $this->actingAs($admin)->get(route('profile.identity', $customer))->assertOk();
        $this->actingAs($otherCustomer)->get(route('profile.identity', $customer))->assertForbidden();
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
