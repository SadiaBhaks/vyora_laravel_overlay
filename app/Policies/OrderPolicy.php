<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(?User $user, Order $order): bool
    {
        if ($user) {
            return $user->isStaff() || $order->user_id === $user->id;
        }

        // Guests may only see an order they just placed in this browser session.
        return in_array($order->number, session('vyora.guest_orders', []), true);
    }

    public function update(User $user, Order $order): bool { return $user->isStaff(); }
    public function cancel(User $user, Order $order): bool
    {
        return ($order->user_id === $user->id || $user->isStaff()) && in_array($order->status, ['pending', 'processing', 'packed'], true);
    }
}
