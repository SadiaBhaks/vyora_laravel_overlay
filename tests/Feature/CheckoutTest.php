<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutException;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStore;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase, BuildsStore;

    public function test_order_is_created_with_server_side_totals_and_reserves_stock(): void
    {
        $v = $this->makeProduct(['price' => 30], stock: 5);
        $order = $this->placeOrderFor($this->makeUser(), $v, 2);
        $this->assertSame(6000, $order->subtotal_cents);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(3, $v->fresh()->stock);
        $this->assertSame('order', InventoryMovement::latest('id')->first()->reason);
        $this->assertCount(1, $order->items);
        $this->assertSame(0, array_sum(app(CartService::class)->raw())); // cart cleared once the order exists
    }

    public function test_order_item_snapshots_price_at_purchase(): void
    {
        $v = $this->makeProduct(['price' => 30]);
        $order = $this->placeOrderFor($this->makeUser(), $v);
        $v->product->update(['price' => 99]);
        $this->assertSame(3000, $order->fresh()->items->first()->unit_price_cents);
    }

    public function test_cannot_oversell(): void
    {
        $v = $this->makeProduct(stock: 1);
        $user = $this->makeUser();
        $this->actingAs($user);
        app(CartService::class)->add($v->id);
        $v->update(['stock' => 0]); // someone else bought it meanwhile
        $this->expectException(CheckoutException::class);
        app(CheckoutService::class)->placeOrder(['email' => $user->email, 'address' => $this->address(), 'shipping_method' => 'standard', 'coupon' => null, 'idempotency_key' => 'k1', 'payment_provider' => 'demo'], $user);
    }

    public function test_idempotency_key_prevents_duplicate_orders(): void
    {
        $v = $this->makeProduct(stock: 5);
        $user = $this->makeUser();
        $a = $this->placeOrderFor($user, $v, 1, 'demo', null, 'same-key');
        $b = $this->placeOrderFor($user, $v, 1, 'demo', null, 'same-key');
        $this->assertSame($a->id, $b->id);
        $this->assertSame(1, Order::count());
        $this->assertSame(4, $v->fresh()->stock);
    }

    public function test_empty_cart_is_rejected(): void
    {
        $this->expectException(CheckoutException::class);
        app(CheckoutService::class)->placeOrder(['email' => 'a@example.test', 'address' => $this->address(), 'shipping_method' => 'standard', 'coupon' => null, 'idempotency_key' => 'e', 'payment_provider' => 'demo'], null);
    }

    public function test_demo_payment_marks_order_as_demo_and_paid_only_after_confirmation(): void
    {
        config(['vyora.demo_checkout' => true]);
        $user = $this->makeUser();
        $order = $this->placeOrderFor($user, $this->makeProduct(), 1, 'demo');
        $this->assertTrue($order->is_demo);
        $this->assertSame('pending', $order->payment_status);
        $this->actingAs($user)->post(route('checkout.demo.confirm', $order))->assertRedirect(route('orders.confirmation', $order));
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertTrue($order->is_demo);
    }

    public function test_confirmation_page_does_not_claim_paid_for_pending_orders(): void
    {
        $user = $this->makeUser();
        $order = $this->placeOrderFor($user, $this->makeProduct(), 1, 'stripe');
        $this->actingAs($user)->get(route('orders.confirmation', $order))->assertOk()->assertSee('confirming your payment')->assertDontSee('Order confirmed');
    }

    public function test_cancelling_unpaid_order_restocks_exactly_once(): void
    {
        $v = $this->makeProduct(stock: 5);
        $order = $this->placeOrderFor($this->makeUser(), $v, 2);
        $svc = app(OrderService::class);
        $svc->cancel($order);
        $svc->cancel($order->fresh());
        $this->assertSame(5, $v->fresh()->stock);
    }

    public function test_only_paid_orders_can_be_fulfilled(): void
    {
        $order = $this->placeOrderFor($this->makeUser(), $this->makeProduct());
        $this->expectException(\DomainException::class);
        app(OrderService::class)->advance($order, 'shipped');
    }
}
