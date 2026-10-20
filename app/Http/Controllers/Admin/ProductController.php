<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::withTrashed()->with(['brand', 'category', 'allVariants'])->latest('id');
        if ($q = trim((string) $request->query('q'))) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($q)).'%';
            $query->where(fn ($w) => $w->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like])->orWhereRaw("LOWER(sku) LIKE ? ESCAPE '!'", [$like]));
        }
        if ($status = $request->query('status')) {
            $status === 'deleted' ? $query->onlyTrashed() : $query->where('status', $status)->whereNull('deleted_at');
        }

        return view('admin.products.index', ['products' => $query->paginate(20)->withQueryString(), 'q' => $q, 'status' => $status]);
    }

    public function create()
    {
        return view('admin.products.form', $this->formData(new Product(['status' => 'draft'])));
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', $this->formData($product));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($this->productAttributes($request, $data));
            $this->syncRelations($product, $request);

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)->with('toast', 'Product created.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->rules($product));
        DB::transaction(function () use ($request, $data, $product) {
            $product->update($this->productAttributes($request, $data, $product));
            $this->syncRelations($product, $request);
        });

        return redirect()->route('admin.products.edit', $product)->with('toast', 'Product saved.');
    }

    /** Products that appear on past orders are archived (history stays intact); others are soft-deleted. */
    public function destroy(Product $product)
    {
        $hasOrders = DB::table('order_items')->where('product_id', $product->id)->exists();
        if ($hasOrders) {
            $product->update(['status' => 'archived']);
            $message = 'Product archived (it appears on past orders).';
        } else {
            $product->delete();
            $message = 'Product deleted.';
        }

        return redirect()->route('admin.products.index')->with('toast', $message);
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);
        if ($image->path) {
            Storage::disk('public')->delete($image->path);
        }
        $image->delete();

        return back()->with('toast', 'Image removed.');
    }

    private function rules(?Product $product = null): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'alpha_dash', 'max:200', Rule::unique('products', 'slug')->ignore($product?->id)],
            'sku' => ['required', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($product?->id)],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'short_description' => ['required', 'string', 'max:300'],
            'description' => ['required', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price', 'max:1000000'],
            'status' => ['required', 'in:active,draft,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
            'materials' => ['nullable', 'string', 'max:255'],
            'features' => ['nullable', 'string', 'max:3000'],
            'specifications' => ['nullable', 'string', 'max:3000'],
            'attributes_text' => ['nullable', 'string', 'max:3000'],
            'tags' => ['nullable', 'string', 'max:500'],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => ['integer', 'exists:collections,id'],
            'variants' => ['required', 'array', 'min:1', 'max:60'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.color' => ['nullable', 'string', 'max:40'],
            'variants.*.color_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'variants.*.size' => ['nullable', 'string', 'max:20'],
            'variants.*.price_override' => ['nullable', 'numeric', 'min:0.01'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'new_images' => ['nullable', 'array', 'max:10'],
            // Real image types only, size-capped; the file is re-stored under a random name.
            'new_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_alt' => ['nullable', 'string', 'max:190'],
            'image_urls' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function productAttributes(Request $request, array $data, ?Product $product = null): array
    {
        $lines = fn (?string $text) => collect(preg_split('/\R/', (string) $text))->map('trim')->filter()->values();
        $pairs = fn (?string $text) => $lines($text)->mapWithKeys(function ($l) {
            [$k, $v] = array_pad(explode(':', $l, 2), 2, '');

            return [trim($k) => trim($v)];
        })->filter()->all();

        $status = $data['status'];
        $published = $product?->published_at ?? ($status === 'active' ? now() : null);

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'sku' => $data['sku'],
            'brand_id' => $data['brand_id'],
            'category_id' => $data['category_id'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'status' => $status,
            'is_featured' => $request->boolean('is_featured'),
            'is_new' => $request->boolean('is_new'),
            'materials' => $data['materials'] ?? null,
            'features' => $lines($data['features'] ?? '')->all(),
            'specifications' => $pairs($data['specifications'] ?? ''),
            'published_at' => $published,
        ];
    }

    private function syncRelations(Product $product, Request $request): void
    {
        // Tags
        $tagIds = collect(explode(',', (string) $request->input('tags')))->map(fn ($t) => trim($t))->filter()->unique()
            ->map(fn ($name) => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => Str::title($name)])->id)->all();
        $product->tags()->sync($tagIds);

        // Collections
        $product->collections()->sync($request->input('collection_ids', []));

        // Attributes (filterable facets)
        $product->attributes()->delete();
        foreach (preg_split('/\R/', (string) $request->input('attributes_text')) as $line) {
            [$name, $value] = array_pad(explode(':', $line, 2), 2, '');
            if (trim($name) !== '' && trim($value) !== '') {
                $product->attributes()->create(['name' => trim($name), 'value' => trim($value)]);
            }
        }

        // Variants: update by id, create new, remove the rest (deactivate if sold before)
        $keep = [];
        foreach ($request->input('variants', []) as $i => $row) {
            $stock = (int) $row['stock'];
            $attrs = [
                'color' => $row['color'] ?: null, 'color_hex' => $row['color_hex'] ?: null, 'size' => $row['size'] ?: null,
                'price_override' => $row['price_override'] ?: null, 'is_active' => true,
            ];
            $variant = ! empty($row['id']) ? $product->allVariants()->find($row['id']) : null;
            if ($variant) {
                $variant->update($attrs);
                if ($variant->stock !== $stock) {
                    InventoryMovement::apply($variant, $stock - $variant->stock, 'adjustment', null, $request->user()->id, 'Edited on product form');
                }
            } else {
                $variant = $product->allVariants()->create($attrs + ['sku' => $product->sku.'-'.Str::upper(Str::random(5)), 'stock' => 0]);
                if ($stock > 0) {
                    InventoryMovement::apply($variant, $stock, 'restock', null, $request->user()->id, 'Initial stock');
                }
            }
            $keep[] = $variant->id;
        }
        foreach ($product->allVariants()->whereNotIn('id', $keep)->get() as $old) {
            $sold = DB::table('order_items')->where('product_variant_id', $old->id)->exists();
            $sold ? $old->update(['is_active' => false]) : $old->delete();
        }

        // Images
        $max = (int) $product->images()->max('sort_order');
        foreach ($request->file('new_images', []) as $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'alt' => $request->input('image_alt') ?: $product->name,
                'sort_order' => ++$max,
            ]);
        }
        foreach (preg_split('/\R/', (string) $request->input('image_urls')) as $url) {
            $url = trim($url);
            if ($url !== '' && preg_match('#^https://#i', $url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $product->images()->create(['url' => $url, 'alt' => $request->input('image_alt') ?: $product->name, 'sort_order' => ++$max]);
            }
        }
    }

    private function formData(Product $product): array
    {
        $product->loadMissing(['images', 'allVariants', 'tags', 'attributes', 'collections']);

        return [
            'product' => $product,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'collections' => Collection::orderBy('name')->get(),
        ];
    }
}
