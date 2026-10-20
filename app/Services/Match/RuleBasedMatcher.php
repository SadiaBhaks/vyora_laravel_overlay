<?php

namespace App\Services\Match;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Deterministic, rule-based matching over the real catalogue. This is NOT an AI model and the UI says so.
 */
class RuleBasedMatcher implements ProductMatcher
{
    public const STYLES = [
        'minimal' => 'Minimal', 'premium' => 'Premium', 'sporty' => 'Sporty',
        'futuristic' => 'Futuristic', 'practical' => 'Practical', 'sustainable' => 'Sustainable',
    ];

    public const FEATURES = [
        'wireless' => 'Wireless', 'portable' => 'Portable', 'long-battery' => 'Long battery',
        'waterproof' => 'Water-resistant', 'smart-home' => 'Smart-home ready', 'ergonomic' => 'Ergonomic',
        'eco-friendly' => 'Eco-friendly', 'travel' => 'Travel-friendly',
    ];

    public function match(array $answers, int $limit = 6): Collection
    {
        $query = Product::query()->active()->inStock()->with(['images', 'brand', 'category', 'variants', 'promotions', 'tags']);

        $category = $answers['category'] ?? null;
        $categoryName = null;
        if ($category && $category !== 'any') {
            $cat = Category::where('slug', $category)->first();
            if ($cat) {
                $query->where('category_id', $cat->id);
                $categoryName = $cat->name;
            }
        }

        $min = (int) ($answers['budget_min'] ?? 0);
        $max = isset($answers['budget_max']) ? (int) $answers['budget_max'] : null;

        $candidates = $query->get()->filter(function (Product $p) use ($min, $max) {
            $price = (float) $p->effective_price;

            return $price >= $min && ($max === null || $price <= $max);
        });

        $style = $answers['style'] ?? null;
        $features = array_values(array_intersect($answers['features'] ?? [], array_keys(self::FEATURES)));
        $gift = ($answers['recipient'] ?? 'me') === 'gift';

        return $candidates->map(function (Product $p) use ($style, $features, $gift, $categoryName, $max, $min) {
            $slugs = $p->tags->pluck('slug')->all();
            $score = 0.0;
            $reasons = [];

            if ($categoryName) {
                $score += 20;
                $reasons[] = "It's in $categoryName, the category you chose.";
            }
            if ($max !== null || $min > 0) {
                $score += 10;
                $reasons[] = 'It fits your budget at '.\App\Support\Money::format(\App\Support\Money::toCents($p->effective_price)).'.';
            }
            if ($style && in_array($style, $slugs, true)) {
                $score += 25;
                $reasons[] = 'It is tagged "'.(self::STYLES[$style] ?? $style).'", matching your style.';
            }
            $hit = array_values(array_intersect($features, $slugs));
            foreach ($hit as $f) {
                $score += 12;
            }
            if ($hit) {
                $reasons[] = 'It offers: '.collect($hit)->map(fn ($f) => self::FEATURES[$f])->implode(', ').'.';
            }
            if ($p->rating_count >= 3 && $p->rating_avg >= 4.3) {
                $score += 8 + ($p->rating_avg - 4.3) * 5;
                $reasons[] = 'Rated '.number_format($p->rating_avg, 1).'/5 across '.$p->rating_count.' reviews.';
            }
            if ($gift && $p->rating_avg >= 4.0) {
                $score += 6;
                $reasons[] = 'A well-reviewed pick for gifting.';
            }
            if ($p->sales_count >= 5) {
                $score += 4;
                $reasons[] = 'Popular with other shoppers.';
            }
            if (! $reasons) {
                $reasons[] = 'A general fit based on the catalogue; add more preferences to sharpen the match.';
            }

            return ['product' => $p, 'score' => round($score, 2), 'reasons' => $reasons];
        })->sortByDesc('score')->values()->take($limit);
    }
}
