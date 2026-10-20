<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStore;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase, BuildsStore;

    public function test_guests_are_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_customers_cannot_open_admin(): void
    {
        $this->actingAs($this->makeUser())->get('/admin')->assertForbidden();
        $this->actingAs($this->makeUser())->get('/admin/orders')->assertForbidden();
    }

    public function test_staff_can_open_dashboard_but_not_settings_or_roles(): void
    {
        $staff = $this->makeUser('staff');
        $this->actingAs($staff)->get('/admin')->assertOk();
        $this->actingAs($staff)->get('/admin/settings')->assertForbidden();
        $target = $this->makeUser();
        $this->actingAs($staff)->put("/admin/customers/{$target->id}/role", ['role' => 'admin'])->assertForbidden();
        $this->assertSame('customer', $target->fresh()->role);
    }

    public function test_admin_can_change_roles_but_not_their_own(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser();
        $this->actingAs($admin)->put("/admin/customers/{$target->id}/role", ['role' => 'staff'])->assertSessionHasNoErrors();
        $this->assertSame('staff', $target->fresh()->role);
        $this->actingAs($admin)->put("/admin/customers/{$admin->id}/role", ['role' => 'customer'])->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }

    public function test_admin_pages_render(): void
    {
        $this->makeProduct();
        $this->actingAs($this->makeUser('admin'));
        foreach (['/admin', '/admin/products', '/admin/products/create', '/admin/orders', '/admin/customers', '/admin/inventory', '/admin/reviews', '/admin/newsletter', '/admin/categories', '/admin/brands', '/admin/collections', '/admin/coupons', '/admin/promotions', '/admin/settings'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
