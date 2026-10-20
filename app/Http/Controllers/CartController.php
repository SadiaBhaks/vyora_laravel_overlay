<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /** "Buy now": add one variant (validated against live stock) and go straight to checkout. */
    public function buyNow(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'between:1,'.config('vyora.max_cart_quantity')],
        ]);

        $result = $cart->add((int) $data['variant_id'], (int) $data['quantity']);
        if (! $result['ok']) {
            return back()->withErrors(['variant_id' => $result['message']]);
        }

        return redirect()->route('checkout');
    }
}
