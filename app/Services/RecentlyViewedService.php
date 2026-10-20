<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductView;
use App\Models\RecentlyViewed;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RecentlyViewedService
{
    private const SESSION_KEY = 'vyora.recent';

    public function record(Product $product): void
    {
        $limit = config('vyora.recently_viewed_limit');
        $visitor = hash('sha256', (Auth::id() ?? 'guest').'|'.session()->getId());
        $already = ProductView::where('product_id', $product->id)->where('visitor_hash', $visitor)
            ->where('viewed_at', '>', now()->subHour())->exists();
        if (! $already) {
            ProductView::create(['product_id' => $product->id, 'visitor_hash' => $visitor, 'viewed_at' => now()]);
            Product::whereKey($product->id)->increment('view_count');
        }

        $ids = array_values(array_diff(session(self::SESSION_KEY, []), [$product->id]));
        array_unshift($ids, $product->id);
        session([self::SESSION_KEY => array_slice($ids, 0, $limit)]);

        if ($user = Auth::user()) {
            RecentlyViewed::updateOrCreate(['user_id' => $user->id, 'product_id' => $product->id], ['viewed_at' => now()]);
            $keep = RecentlyViewed::where('user_id', $user->id)->orderByDesc('viewed_at')->limit($limit)->pluck('id');
            RecentlyViewed::where('user_id', $user->id)->whereNotIn('id', $keep)->delete();
        }
    }

    public function ids(int $limit = 8): array
    {
        if ($user = Auth::user()) {
            $db = RecentlyViewed::where('user_id', $user->id)->orderByDesc('viewed_at')->limit($limit)->pluck('product_id')->all();
            if ($db) {
                return $db;
            }
        }

        return array_slice(session(self::SESSION_KEY, []), 0, $limit);
    }

    public function products(int $limit = 8, ?int $excludeId = null): Collection
    {
        $ids = array_values(array_diff($this->ids($limit + 1), [$excludeId]));
        $ids = array_slice($ids, 0, $limit);
        if (! $ids) {
            return collect();
        }
        $products = Product::active()->with(['images', 'brand', 'variants', 'promotions', 'tags'])->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)->map(fn ($id) => $products->get($id))->filter()->values();
    }

    public function syncSessionToUser(User $user): void
    {
        foreach (array_reverse(session(self::SESSION_KEY, [])) as $id) {
            if (Product::whereKey($id)->exists()) {
                RecentlyViewed::updateOrCreate(['user_id' => $user->id, 'product_id' => $id], ['viewed_at' => now()]);
            }
        }
    }
}
