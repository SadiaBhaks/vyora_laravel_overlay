<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\WishlistService;

class WishlistController extends Controller
{
    public function toggle(Product $product, WishlistService $wishlist)
    {
        abort_unless($product->status === 'active', 404);
        $added = $wishlist->toggle($product->id);

        return response()->json([
            'added' => $added,
            'ids' => $wishlist->ids(),
            'message' => $added ? 'Saved to your wishlist.' : 'Removed from your wishlist.',
        ]);
    }
}
