<?php

namespace App\Services;

use App\Models\User;

/** After login/registration, bring the anonymous session's cart, wishlist and history into the account. */
class GuestMerger
{
    public function __construct(private CartService $cart, private WishlistService $wishlist, private RecentlyViewedService $recent) {}

    public function merge(User $user, array $guestCart, array $guestWishlist): void
    {
        $this->cart->mergeGuestCart($user, $guestCart);
        $this->wishlist->syncGuestToUser($user, $guestWishlist);
        $this->recent->syncSessionToUser($user);
        session()->forget(['vyora.cart', 'vyora.wishlist']);
    }
}
