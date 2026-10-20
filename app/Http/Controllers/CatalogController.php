<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\RecommendationService;
use App\Services\SearchService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function shop() { return view('pages.shop', ['title' => 'Shop everything', 'mode' => 'shop']); }

    public function categories()
    {
        return view('pages.categories', ['categories' => Category::active()->withCount(['products' => fn ($q) => $q->active()])->get()]);
    }

    public function category(string $slug)
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();

        return view('pages.category', ['category' => $category]);
    }

    public function newArrivals() { return view('pages.new-arrivals'); }

    public function search(Request $request, SearchService $search)
    {
        $q = SearchService::clean($request->query('q'));
        if ($q !== '') {
            $search->remember($q);
        }

        return view('pages.search', ['q' => $q]);
    }

    public function deals()
    {
        $promotions = Promotion::running()->with(['products' => fn ($q) => $q->active()->with(['images', 'brand', 'variants', 'promotions'])])->orderBy('ends_at')->get();
        $coupons = \App\Models\Coupon::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->whereNotNull('description')->get()->filter->isCurrentlyValid();
        $onSale = Product::active()->onSale()->with(['images', 'brand', 'variants', 'promotions', 'category'])->orderByDesc('rating_avg')->get();

        return view('pages.deals', [
            'promotions' => $promotions,
            'coupons' => $coupons,
            'onSale' => $onSale,
            'dealCategories' => $onSale->pluck('category')->unique('id')->values(),
            'popular' => $onSale->sortByDesc(fn ($p) => $p->sales_count * 3 + $p->view_count)->take(4),
        ]);
    }

    public function trending(RecommendationService $recs)
    {
        $with = ['images', 'brand', 'variants', 'promotions'];
        $popular = $recs->trending(10);
        $ids = $popular->pluck('id')->all();

        return view('pages.trending', [
            'popular' => $popular,
            'bestSellers' => Product::active()->with($with)->where('sales_count', '>', 0)->orderByDesc('sales_count')->limit(6)->get(),
            'topRated' => Product::active()->with($with)->where('rating_count', '>=', 3)->orderByDesc('rating_avg')->orderByDesc('rating_count')->limit(6)->get(),
            'recentlyPopular' => Product::active()->with($with)->select('products.*')
                ->selectRaw('(SELECT COUNT(*) FROM product_views pv WHERE pv.product_id = products.id AND pv.viewed_at >= ?) as week_views', [now()->subDays(7)->toDateTimeString()])
                ->orderByDesc('week_views')->limit(6)->get(),
            'rising' => $recs->risingProducts(6),
            'editorPicks' => Product::active()->with($with)->where('is_featured', true)->orderByDesc('rating_avg')->limit(6)->get(),
        ]);
    }
}
