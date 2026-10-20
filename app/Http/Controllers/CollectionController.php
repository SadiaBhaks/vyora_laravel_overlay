<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;

class CollectionController extends Controller
{
    public function index()
    {
        return view('pages.collections', [
            'collections' => Collection::active()->withCount(['products' => fn ($q) => $q->active()])->with(['products' => fn ($q) => $q->active()->with('images')])->get(),
        ]);
    }

    public function show(Collection $collection)
    {
        abort_unless($collection->is_active, 404);
        $products = $collection->products()->active()->with(['images', 'brand', 'variants', 'promotions', 'tags', 'category'])->get();
        $tagIds = $products->flatMap->tags->pluck('id')->unique()->all();
        $productIds = $products->pluck('id')->all();

        $complementary = Product::active()->inStock()->with(['images', 'brand', 'variants', 'promotions'])
            ->whereNotIn('products.id', $productIds)
            ->whereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds ?: [0]))
            ->orderByDesc('rating_avg')->limit(4)->get();

        return view('pages.collection', [
            'collection' => $collection,
            'products' => $products,
            'complementary' => $complementary,
            'others' => Collection::active()->where('id', '!=', $collection->id)->limit(3)->get(),
        ]);
    }
}
