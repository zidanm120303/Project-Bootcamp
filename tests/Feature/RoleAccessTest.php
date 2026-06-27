<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_open_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_has_separate_management_page_for_each_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        User::factory()->create(['role' => 'mitra']);
        User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin)
            ->get(route('admin.users.admin'))
            ->assertOk()
            ->assertSee('Pengguna Admin');

        $this->actingAs($admin)
            ->get(route('admin.users.mitra'))
            ->assertOk()
            ->assertSee('Pengguna Mitra');

        $this->actingAs($admin)
            ->get(route('admin.users.customer'))
            ->assertOk()
            ->assertSee('Pengguna Customer');
    }

    public function test_suspended_user_is_logged_out_by_role_middleware(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'suspended']);
        $this->actingAs($customer)->get(route('customer.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
