<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Demo catalogue: fictional brands/products, artwork generated locally. Safe to re-run (idempotent by slug/SKU). */
class CatalogSeeder extends Seeder
{
    private const CATEGORIES = [
        'fashion' => ['Fashion', 'Pieces built to be worn on repeat.'],
        'electronics' => ['Electronics', 'Everyday tech that earns its place.'],
        'accessories' => ['Accessories', 'The small things that finish a look.'],
        'beauty' => ['Beauty', 'Simple routines, honest formulas.'],
        'home' => ['Home', 'Considered objects for slower living.'],
        'fitness' => ['Fitness', 'Gear that keeps up with you.'],
        'lifestyle' => ['Lifestyle', 'For trips, mornings and everything between.'],
        'smart-living' => ['Smart Living', 'A home that quietly works for you.'],
    ];

    public function run(): void
    {
        $catalog = require __DIR__.'/data/catalog.php';
        $order = 0;
        foreach (self::CATEGORIES as $slug => [$name, $tagline]) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name, 'tagline' => $tagline, 'description' => $tagline.' (Demo catalogue.)', 'art_seed' => 'cat-'.$slug, 'sort_order' => ++$order, 'is_active' => true]);
        }

        $n = 0;
        foreach ($catalog as $catSlug => $rows) {
            $category = Category::where('slug', $catSlug)->firstOrFail();
            foreach ($rows as $i => [$name, $brandName, $price, $compare, $short, $tags, $colors, $sizes, $attrs, $flags]) {
                $n++;
                $slug = Str::slug($name);
                $brand = Brand::firstOrCreate(['slug' => Str::slug($brandName)], ['name' => $brandName, 'description' => $brandName.' is a fictional demo brand.']);
                $product = Product::withTrashed()->updateOrCreate(['slug' => $slug], [
                    'brand_id' => $brand->id, 'category_id' => $category->id, 'name' => $name,
                    'sku' => 'VY-'.strtoupper(substr($catSlug, 0, 3)).'-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                    'short_description' => $short,
                    'description' => $short."\n\nThis is a demo-catalogue product used to showcase the VYORA storefront. Specifications and pricing are illustrative.",
                    'price' => $price, 'compare_at_price' => $compare, 'status' => 'active',
                    'is_new' => str_contains($flags, 'n'), 'is_featured' => str_contains($flags, 'f'),
                    'features' => collect($attrs)->map(fn ($v, $k) => "$k: $v")->values()->all(),
                    'specifications' => $attrs,
                    'materials' => $attrs['Material'] ?? null,
                    'published_at' => str_contains($flags, 'n') ? now()->subDays(3 + $n % 20) : now()->subDays(45 + $n * 3),
                ]);
                $product->restore();

                $product->tags()->sync(collect($tags)->map(fn ($t) => Tag::firstOrCreate(['slug' => $t], ['name' => Str::headline($t)])->id)->all());

                $product->attributes()->delete();
                foreach ($attrs as $k => $v) {
                    $product->attributes()->create(['name' => $k, 'value' => $v]);
                }
                foreach ($tags as $t) {
                    if (in_array($t, ['minimal', 'premium', 'sporty', 'futuristic', 'practical', 'sustainable'], true)) {
                        $product->attributes()->create(['name' => 'Style', 'value' => Str::headline($t)]);
                    }
                }

                $product->images()->delete();
                foreach ([1, 2, 3] as $k) {
                    $product->images()->create(['art_seed' => $slug.'-'.$k, 'alt' => $name.' — illustrative artwork '.$k, 'sort_order' => $k]);
                }

                $colorList = $colors ?: [null => null];
                $sizeList = $sizes ?: [null];
                foreach ($colorList as $cName => $hex) {
                    foreach ($sizeList as $size) {
                        $sku = $product->sku.'-'.Str::upper(Str::substr(Str::slug((string) $cName), 0, 3)).'-'.$size;
                        $variant = ProductVariant::firstOrCreate(['sku' => rtrim($sku, '-')], ['product_id' => $product->id, 'color' => $cName ?: null, 'color_hex' => $hex, 'size' => $size, 'stock' => 0, 'is_active' => true]);
                        if ($variant->wasRecentlyCreated) {
                            // Deterministic stock so the demo is stable; a few are deliberately low/out for the UI states.
                            $stock = (($n * 7 + strlen((string) $size) * 3 + strlen((string) $cName)) % 9 === 0) ? 0 : 6 + (($n * 5) % 30);
                            if ($n % 11 === 0 && $size === ($sizeList[0])) { $stock = 3; }
                            InventoryMovement::apply($variant, $stock, 'seed', null, null, 'Demo seed stock');
                        }
                    }
                }
            }
        }
    }
}
