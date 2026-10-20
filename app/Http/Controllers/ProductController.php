<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Services\RecentlyViewedService;
use App\Services\RecommendationService;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Request $request, Product $product, RecommendationService $recs, RecentlyViewedService $recent, ReviewService $reviews)
    {
        abort_unless($product->status === 'active' && (! $product->published_at || $product->published_at->lte(now())), 404);

        $product->load(['images', 'brand', 'category', 'variants', 'promotions', 'tags', 'attributes']);
        $recent->record($product);

        $sort = in_array($request->query('review_sort'), ['newest', 'highest', 'lowest'], true) ? $request->query('review_sort') : 'newest';
        $reviewQuery = $product->approvedReviews()->with('user:id,name');
        match ($sort) {
            'highest' => $reviewQuery->orderByDesc('rating')->latest(),
            'lowest' => $reviewQuery->orderBy('rating')->latest(),
            default => $reviewQuery->latest(),
        };

        $breakdown = $product->approvedReviews()->selectRaw('rating, COUNT(*) as c')->groupBy('rating')->pluck('c', 'rating');
        $user = $request->user();
        $shown = [$product->id];
        $related = $recs->related($product, 4);
        $look = $recs->completeTheLook($product, 4, $related->pluck('id')->all());

        return view('pages.product', [
            'product' => $product,
            'reviews' => $reviewQuery->paginate(5, ['*'], 'reviews')->withQueryString(),
            'reviewSort' => $sort,
            'breakdown' => $breakdown,
            'related' => $related,
            'look' => $look,
            'recent' => $recent->products(6, $product->id),
            'canReview' => $user && $reviews->eligibleOrder($user, $product) && ! $reviews->alreadyReviewed($user, $product),
            'hasReviewed' => $user && $reviews->alreadyReviewed($user, $product),
            'delivery' => app(\App\Services\PricingService::class)->deliveryWindow('standard'),
        ]);
    }

    public function storeReview(ReviewRequest $request, Product $product, ReviewService $reviews)
    {
        $user = $request->user();
        $order = $reviews->eligibleOrder($user, $product);
        abort_unless($order, 403, 'Only customers who received this product can review it.');
        if ($reviews->alreadyReviewed($user, $product)) {
            return back()->withErrors(['body' => 'You have already reviewed this product.']);
        }

        Review::create($request->safe()->only(['rating', 'title', 'body']) + [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'is_verified' => true,
            'status' => 'pending', // moderated before it is shown
        ]);

        return back()->with('toast', 'Thanks! Your verified review is awaiting moderation.');
    }
}
