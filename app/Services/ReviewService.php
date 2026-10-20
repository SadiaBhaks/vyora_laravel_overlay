<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewService
{
    /** Only buyers whose order was paid and has shipped may review — the order is recorded on the review. */
    public function eligibleOrder(?User $user, Product $product): ?Order
    {
        if (! $user) {
            return null;
        }

        return Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['shipped', 'out_for_delivery', 'delivered'])
            ->whereHas('items', fn ($i) => $i->where('product_id', $product->id))
            ->latest('placed_at')->first();
    }

    public function alreadyReviewed(User $user, Product $product): bool
    {
        return Review::where(['user_id' => $user->id, 'product_id' => $product->id])->exists();
    }
}
