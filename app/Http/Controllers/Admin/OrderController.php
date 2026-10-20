<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with('user')->latest('placed_at');
        if ($s = $request->query('status')) { $query->where('status', $s); }
        if ($p = $request->query('payment')) { $query->where('payment_status', $p); }
        if ($q = trim((string) $request->query('q'))) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($q)).'%';
            $query->where(fn ($w) => $w->whereRaw("LOWER(number) LIKE ? ESCAPE '!'", [$like])->orWhereRaw("LOWER(email) LIKE ? ESCAPE '!'", [$like]));
        }

        return view('admin.orders.index', ['orders' => $query->paginate(20)->withQueryString(), 'filters' => $request->only('q', 'status', 'payment')]);
    }

    public function show(Order $order, OrderService $orders)
    {
        $this->authorize('update', $order);
        $order->load(['items', 'histories', 'payments', 'user']);

        return view('admin.orders.show', ['order' => $order, 'timeline' => $orders->timeline($order), 'flow' => OrderService::FLOW]);
    }

    public function update(Request $request, Order $order, OrderService $orders)
    {
        $this->authorize('update', $order);
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', OrderService::FLOW)],
            'carrier' => ['nullable', 'string', 'max:80'],
            'tracking_number' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:190'],
        ]);

        if ($order->status === 'cancelled') {
            return back()->withErrors(['status' => 'Cancelled orders cannot be updated.']);
        }
        try {
            $orders->advance($order, $data['status'], $request->user()->id, $data['note'] ?? null, $data);
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('toast', 'Order updated.');
    }

    public function cancel(Request $request, Order $order, OrderService $orders)
    {
        $this->authorize('update', $order);
        $orders->cancel($order, 'Cancelled by staff. Refund (if paid) must be issued in the Stripe dashboard.', $request->user()->id);

        return back()->with('toast', 'Order cancelled and stock returned.');
    }
}
