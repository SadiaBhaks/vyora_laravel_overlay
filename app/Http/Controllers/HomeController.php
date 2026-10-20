<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\RecentlyViewedService;
use App\Services\RecommendationService;

class HomeController extends Controller
{
    public function __invoke(RecommendationService $recs, RecentlyViewedService $recent)
    {
        $with = ['images', 'brand', 'variants', 'promotions', 'tags'];

        $newArrivals = Product::active()->with($with)->orderByDesc('published_at')->limit(8)->get();
        $shown = $newArrivals->pluck('id')->all();

        $trending = $recs->trending(8, $shown);
        $shown = array_merge($shown, $trending->pluck('id')->all());

        $deals = Product::active()->onSale()->with($with)->whereNotIn('products.id', $shown)->orderByDesc('rating_avg')->limit(4)->get();
        $shown = array_merge($shown, $deals->pluck('id')->all());

        $personal = $recs->forVisitor(auth()->user(), 8, $shown);

        $promotion = Promotion::running()->orderBy('ends_at')->first();

        return view('pages.home', [
            'categories' => Category::active()->withCount(['products' => fn ($q) => $q->active()])->get(),
            'newArrivals' => $newArrivals,
            'trending' => $trending,
            'deals' => $deals,
            'promotion' => $promotion,
            'collections' => Collection::active()->where('is_featured', true)->with(['products' => fn ($q) => $q->active()->with('images')])->limit(7)->get(),
            'personal' => $personal['products'],
            'personalised' => $personal['personalised'],
            'recent' => $recent->products(6),
            'interestCategories' => Category::active()->get(),
        ]);
    }
}
