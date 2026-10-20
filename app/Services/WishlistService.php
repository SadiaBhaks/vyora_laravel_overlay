<?php

namespace App\Services;

use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;

/** Guests keep product IDs in the session; they are synced to the DB on login. */
class WishlistService
{
    private const SESSION_KEY = 'vyora.wishlist';

    public function ids(): array
    {
        if ($user = Auth::user()) {
            return $user->wishlistItems()->pluck('product_id')->all();
        }

        return array_values(session(self::SESSION_KEY, []));
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    /** @return bool true when the product is now in the wishlist */
    public function toggle(int $productId): bool
    {
        if ($user = Auth::user()) {
            $existing = WishlistItem::where(['user_id' => $user->id, 'product_id' => $productId])->first();
            if ($existing) {
                $existing->delete();

                return false;
            }
            WishlistItem::create(['user_id' => $user->id, 'product_id' => $productId]);

            return true;
        }
        $ids = session(self::SESSION_KEY, []);
        if (($key = array_search($productId, $ids, true)) !== false) {
            unset($ids[$key]);
            session([self::SESSION_KEY => array_values($ids)]);

            return false;
        }
        $ids[] = $productId;
        session([self::SESSION_KEY => $ids]);

        return true;
    }

    public function remove(int $productId): void
    {
        if ($user = Auth::user()) {
            WishlistItem::where(['user_id' => $user->id, 'product_id' => $productId])->delete();

            return;
        }
        session([self::SESSION_KEY => array_values(array_diff(session(self::SESSION_KEY, []), [$productId]))]);
    }

    public function syncGuestToUser(User $user, array $guestIds): void
    {
        foreach (array_unique($guestIds) as $id) {
            WishlistItem::firstOrCreate(['user_id' => $user->id, 'product_id' => $id]);
        }
    }
}
