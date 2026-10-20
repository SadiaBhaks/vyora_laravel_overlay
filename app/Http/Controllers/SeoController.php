<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;

class SeoController extends Controller
{
    public function sitemap()
    {
        $urls = collect([route('home'), route('shop'), route('categories'), route('new-arrivals'), route('trending'), route('deals'), route('collections'), route('match'), route('about'), route('contact'), route('faq'), route('shipping'), route('returns')])
            ->map(fn ($u) => ['loc' => $u, 'lastmod' => now()->toDateString()]);

        $urls = $urls
            ->merge(Category::active()->get()->map(fn ($c) => ['loc' => route('categories.show', $c->slug), 'lastmod' => $c->updated_at->toDateString()]))
            ->merge(Collection::active()->get()->map(fn ($c) => ['loc' => route('collections.show', $c), 'lastmod' => $c->updated_at->toDateString()]))
            ->merge(Product::active()->get(['id', 'slug', 'updated_at'])->map(fn ($p) => ['loc' => route('product.show', $p), 'lastmod' => $p->updated_at->toDateString()]));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /account\nDisallow: /cart\nDisallow: /checkout\nDisallow: /order-confirmation\nDisallow: /search\nAllow: /\n\nSitemap: ".route('sitemap')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
