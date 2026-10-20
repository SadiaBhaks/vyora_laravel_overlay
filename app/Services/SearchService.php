<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SearchTerm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    public static function clean(?string $term): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $term))), 0, 80);
    }

    /** Escape LIKE wildcards; the query uses ESCAPE '!' which behaves the same on SQLite, MySQL and PostgreSQL. */
    public static function like(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';
    }

    public function query(string $term): Builder
    {
        $term = self::clean($term);
        $like = self::like($term);

        return Product::query()->active()
            ->where(function (Builder $q) use ($like) {
                $q->whereRaw("LOWER(products.name) LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("LOWER(products.short_description) LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("LOWER(products.description) LIKE ? ESCAPE '!'", [$like])
                    ->orWhereHas('brand', fn ($b) => $b->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like]))
                    ->orWhereHas('category', fn ($c) => $c->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like]))
                    ->orWhereHas('tags', fn ($t) => $t->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like]));
            });
    }

    /** Name matches first, then everything else. Applied by callers that want relevance ordering. */
    public function orderByRelevance(Builder $query, string $term): Builder
    {
        return $query->orderByRaw("CASE WHEN LOWER(products.name) LIKE ? ESCAPE '!' THEN 0 ELSE 1 END", [self::like(self::clean($term))]);
    }

    public function suggest(string $term, int $limit = 6): array
    {
        $term = self::clean($term);
        if (mb_strlen($term) < 2) {
            return ['count' => 0, 'products' => collect()];
        }
        $q = $this->query($term);
        $count = (clone $q)->count();

        return [
            'count' => $count,
            'products' => $this->orderByRelevance($q, $term)->with(['images', 'brand', 'variants', 'promotions'])->orderByDesc('rating_avg')->limit($limit)->get(),
        ];
    }

    public function remember(string $term): void
    {
        $term = mb_strtolower(self::clean($term));
        if (mb_strlen($term) < 2) {
            return;
        }
        $row = SearchTerm::firstOrCreate(['term' => $term], ['hits' => 0]);
        $row->increment('hits');
        Cache::forget('vyora.popular-searches');
    }

    public function popular(int $limit = 6): array
    {
        return Cache::remember('vyora.popular-searches', 600, fn () => SearchTerm::orderByDesc('hits')->limit($limit)->pluck('term')->all());
    }
}
