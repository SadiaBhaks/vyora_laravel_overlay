<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** Used for /order-confirmation/{order} and /account/orders/{order}. Ownership enforced by OrderPolicy. */
    public function show(Request $request, Order $order, OrderService $orders)
    {
        $this->authorize('view', $order);
        $order->load(['items', 'histories', 'payments']);
        $confirmation = $request->routeIs('orders.confirmation');

        return view('orders.show', [
            'order' => $order,
            'timeline' => $orders->timeline($order),
            'confirmation' => $confirmation,
        ]);
    }

    public function cancel(Request $request, Order $order, OrderService $orders)
    {
        $this->authorize('cancel', $order);
        if ($order->payment_status === 'paid') {
            return back()->withErrors(['order' => 'Paid orders are cancelled by our team so the refund is handled properly. Please contact us.']);
        }
        $orders->cancel($order, 'Cancelled by customer.', $request->user()->id);

        return redirect()->route('account.orders.show', $order)->with('toast', 'Order cancelled.');
    }
}
