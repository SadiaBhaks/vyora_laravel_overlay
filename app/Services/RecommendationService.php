<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** All recommendation rules live here so every page uses the same, explainable logic. */
class RecommendationService
{
    public function __construct(private RecentlyViewedService $recent) {}

    private function base()
    {
        return Product::query()->active()->inStock()->with(['images', 'brand', 'category', 'variants', 'promotions', 'tags']);
    }

    /** Trending score: recent views + weighted sales + quality. Real recorded signals only. */
    public function trending(int $limit = 8, array $exclude = []): Collection
    {
        $since = now()->subDays(14)->toDateTimeString();
        $products = $this->base()
            ->whereNotIn('products.id', $exclude)
            ->select('products.*')
            ->selectRaw('(SELECT COUNT(*) FROM product_views pv WHERE pv.product_id = products.id AND pv.viewed_at >= ?) as recent_views', [$since])
            ->get();

        return $products->map(function (Product $p) {
            $p->trend_score = ($p->recent_views * 1.0) + ($p->sales_count * 3.0) + ($p->rating_avg * min($p->rating_count, 20) * 0.5);

            return $p;
        })->sortByDesc('trend_score')->values()->take($limit);
    }

    public function related(Product $product, int $limit = 8, array $exclude = []): Collection
    {
        $tagIds = $product->tags->pluck('id')->all();
        $candidates = $this->base()
            ->where('products.id', '!=', $product->id)
            ->whereNotIn('products.id', $exclude)
            ->where(function ($q) use ($product, $tagIds) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id)
                    ->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds ?: [0]));
            })->limit(80)->get();

        $price = (float) $product->price;

        return $candidates->map(function (Product $c) use ($product, $tagIds, $price) {
            $shared = $c->tags->pluck('id')->intersect($tagIds)->count();
            $priceGap = $price > 0 ? abs((float) $c->price - $price) / $price : 1;
            $c->match_score = $shared * 3
                + ($c->category_id === $product->category_id ? 2 : 0)
                + ($c->brand_id === $product->brand_id ? 1 : 0)
                + max(0, 1.5 - $priceGap)
                + $c->rating_avg * 0.3;

            return $c;
        })->sortByDesc('match_score')->values()->take($limit);
    }

    /** Complementary items: other categories that share tags or collections with this product. */
    public function completeTheLook(Product $product, int $limit = 4, array $exclude = []): Collection
    {
        $tagIds = $product->tags->pluck('id')->all();
        $collectionIds = $product->collections()->pluck('collections.id')->all();

        $candidates = $this->base()
            ->where('products.id', '!=', $product->id)
            ->where('category_id', '!=', $product->category_id)
            ->whereNotIn('products.id', $exclude)
            ->where(function ($q) use ($tagIds, $collectionIds) {
                $q->whereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds ?: [0]))
                    ->orWhereHas('collections', fn ($c) => $c->whereIn('collections.id', $collectionIds ?: [0]));
            })->limit(40)->get();

        return $candidates->map(function (Product $c) use ($tagIds) {
            $c->match_score = $c->tags->pluck('id')->intersect($tagIds)->count() * 2 + $c->rating_avg * 0.4;

            return $c;
        })->sortByDesc('match_score')->groupBy('category_id')->map->first()->values()->take($limit);
    }

    /**
     * Personalised picks from stated interests, browsing, wishlist and purchases.
     * New visitors with no signals get trending products (and the reason says so).
     *
     * @return array{products: Collection, personalised: bool}
     */
    public function forVisitor(?User $user, int $limit = 8, array $exclude = []): array
    {
        $categoryWeights = [];
        $tagWeights = [];

        $viewed = $this->recent->products(12);
        foreach ($viewed as $p) {
            $categoryWeights[$p->category_id] = ($categoryWeights[$p->category_id] ?? 0) + 2;
            foreach ($p->tags ?? [] as $t) {
                $tagWeights[$t->id] = ($tagWeights[$t->id] ?? 0) + 1;
            }
        }
        if ($user) {
            $interests = $user->preferences?->interests ?? [];
            if ($interests) {
                $ids = \App\Models\Category::whereIn('slug', $interests)->pluck('id');
                foreach ($ids as $id) {
                    $categoryWeights[$id] = ($categoryWeights[$id] ?? 0) + 4;
                }
            }
            $bought = DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.user_id', $user->id)->where('orders.payment_status', 'paid')
                ->pluck('products.category_id');
            foreach ($bought as $cid) {
                $categoryWeights[$cid] = ($categoryWeights[$cid] ?? 0) + 3;
            }
            $wished = $user->wishlistProducts()->pluck('category_id');
            foreach ($wished as $cid) {
                $categoryWeights[$cid] = ($categoryWeights[$cid] ?? 0) + 2;
            }
            $exclude = array_merge($exclude, $user->wishlistItems()->pluck('product_id')->all());
        }
        $exclude = array_merge($exclude, $viewed->pluck('id')->all());

        if (! $categoryWeights && ! $tagWeights) {
            return ['products' => $this->trending($limit, $exclude), 'personalised' => false];
        }

        $candidates = $this->base()->whereNotIn('products.id', $exclude)
            ->whereIn('category_id', array_keys($categoryWeights) ?: [0])
            ->limit(80)->get();

        $scored = $candidates->map(function (Product $c) use ($categoryWeights, $tagWeights) {
            $c->match_score = ($categoryWeights[$c->category_id] ?? 0)
                + $c->tags->sum(fn ($t) => $tagWeights[$t->id] ?? 0) * 0.5
                + $c->rating_avg * 0.4;

            return $c;
        })->sortByDesc('match_score')->values();

        // Avoid a wall of one category: at most 3 from any category before topping up.
        $picked = collect();
        $perCategory = [];
        foreach ($scored as $p) {
            if (($perCategory[$p->category_id] ?? 0) >= 3) {
                continue;
            }
            $picked->push($p);
            $perCategory[$p->category_id] = ($perCategory[$p->category_id] ?? 0) + 1;
            if ($picked->count() >= $limit) {
                break;
            }
        }
        if ($picked->count() < $limit) {
            $picked = $picked->merge($this->trending($limit - $picked->count(), array_merge($exclude, $picked->pluck('id')->all())));
        }

        return ['products' => $picked->values(), 'personalised' => true];
    }

    public function risingProducts(int $limit = 8): Collection
    {
        $recent = now()->subDays(7)->toDateTimeString();
        $prior = now()->subDays(14)->toDateTimeString();

        return $this->base()->select('products.*')
            ->selectRaw('(SELECT COUNT(*) FROM product_views pv WHERE pv.product_id = products.id AND pv.viewed_at >= ?) as views_now', [$recent])
            ->selectRaw('(SELECT COUNT(*) FROM product_views pv WHERE pv.product_id = products.id AND pv.viewed_at >= ? AND pv.viewed_at < ?) as views_before', [$prior, $recent])
            ->get()
            ->filter(fn ($p) => $p->views_now > $p->views_before)
            ->sortByDesc(fn ($p) => $p->views_now - $p->views_before)
            ->values()->take($limit);
    }
}
