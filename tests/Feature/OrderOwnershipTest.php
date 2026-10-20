<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStore;
use Tests\TestCase;

class OrderOwnershipTest extends TestCase
{
    use RefreshDatabase, BuildsStore;

    public function test_users_cannot_view_or_cancel_other_users_orders(): void
    {
        $owner = $this->makeUser();
        $order = $this->placeOrderFor($owner, $this->makeProduct());
        $other = $this->makeUser();

        $this->actingAs($owner)->get(route('account.orders.show', $order))->assertOk();
        $this->actingAs($other)->get(route('account.orders.show', $order))->assertForbidden();
        $this->actingAs($other)->get(route('orders.confirmation', $order))->assertForbidden();
        $this->actingAs($other)->post(route('account.orders.cancel', $order))->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_users_cannot_edit_other_users_addresses(): void
    {
        $a = $this->makeUser();
        $b = $this->makeUser();
        $addr = $a->addresses()->create($this->address() + ['label' => 'Home', 'is_default' => true]);
        $this->actingAs($b)->get(route('account.addresses.edit', $addr))->assertForbidden();
        $this->actingAs($b)->delete(route('account.addresses.destroy', $addr))->assertForbidden();
        $this->assertDatabaseHas('addresses', ['id' => $addr->id]);
    }
}
