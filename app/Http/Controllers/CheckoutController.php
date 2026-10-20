<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(CartService $cart)
    {
        if ($cart->count() === 0) {
            return redirect()->route('cart')->with('toast', 'Your cart is empty.');
        }

        return view('checkout.index');
    }

    /** Stripe success_url. Never marks anything paid — only the verified webhook does that. */
    public function returned(Order $order)
    {
        $this->authorize('view', $order);

        return redirect()->route('orders.confirmation', $order);
    }

    /** Stripe cancel_url: the customer backed out, so release their reserved stock. */
    public function cancelled(Order $order, OrderService $orders, CartService $cart)
    {
        $this->authorize('view', $order);
        if ($order->payment_status === 'pending') {
            $orders->markPaymentFailed($order, 'Customer cancelled at payment.');
            // Put the items back so "your cart is still here" is true.
            foreach ($order->items()->whereNotNull('product_variant_id')->get() as $item) {
                $cart->add($item->product_variant_id, $item->quantity);
            }
        }

        return redirect()->route('cart')->with('toast', 'Payment cancelled. Your cart is still here.');
    }

    public function demo(Order $order)
    {
        $this->authorize('view', $order);
        abort_unless($order->is_demo && PaymentGateway::demoEnabled() && $order->payment_status === 'pending', 404);

        return view('checkout.demo', ['order' => $order->load('items')]);
    }

    /** Demo mode only: records a clearly-labelled demo payment. No card is charged. */
    public function confirmDemo(Request $request, Order $order, OrderService $orders)
    {
        $this->authorize('view', $order);
        abort_unless($order->is_demo && PaymentGateway::demoEnabled(), 404);
        $orders->markPaid($order, null, 'demo');

        return redirect()->route('orders.confirmation', $order);
    }
}
