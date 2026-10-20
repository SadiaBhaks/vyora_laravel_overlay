<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Services\SearchService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Database-driven catalogue grid. Filters and sorting are query parameters, so every view is shareable. */
class ProductListing extends Component
{
    use WithPagination;

    #[Locked] public string $mode = 'shop';          // shop | category | collection | new | deals | search
    #[Locked] public ?string $scope = null;          // category or collection slug when the mode is fixed

    #[Url(as: 'q', except: '')] public string $q = '';
    #[Url(except: [])] public array $categories = [];
    #[Url(except: [])] public array $brands = [];
    #[Url(except: '')] public string $min = '';
    #[Url(except: '')] public string $max = '';
    #[Url(except: 0)] public int $rating = 0;
    #[Url(except: 0)] public int $discount = 0;
    #[Url(except: false)] public bool $inStock = false;
    #[Url(except: [])] public array $colors = [];
    #[Url(except: [])] public array $sizes = [];
    #[Url(except: [])] public array $attrs = [];     // "Name::Value"
    #[Url(except: '')] public string $sort = '';
    #[Url(except: 'grid')] public string $layout = 'grid';

    public bool $filtersOpen = false;
    #[Locked] public int $perPage = 12;

    private const SORTS = ['recommended', 'newest', 'price_asc', 'price_desc', 'rating', 'popular'];

    public function updated($name): void
    {
        if ($name === 'layout' || $name === 'filtersOpen') {
            return;
        }
        $this->resetPage();
    }

    public function paginationView() { return 'pagination.vyora-livewire'; }

    public function clearAll(): void
    {
        $this->reset(['categories', 'brands', 'min', 'max', 'rating', 'discount', 'inStock', 'colors', 'sizes', 'attrs']);
        $this->resetPage();
    }

    private const FILTER_KEYS = ['categories', 'brands', 'min', 'max', 'rating', 'discount', 'inStock', 'colors', 'sizes', 'attrs'];

    public function removeFilter(string $key, ?string $value = null): void
    {
        if (! in_array($key, self::FILTER_KEYS, true)) {
            return; // never let the browser pick an arbitrary property
        }
        if (is_array($this->{$key})) {
            $this->{$key} = array_values(array_diff($this->{$key}, [$value]));
        } elseif (is_bool($this->{$key})) {
            $this->{$key} = false;
        } elseif (is_int($this->{$key})) {
            $this->{$key} = 0;
        } else {
            $this->{$key} = '';
        }
        $this->resetPage();
    }

    private function effectiveSort(): string
    {
        if (in_array($this->sort, self::SORTS, true)) {
            return $this->sort;
        }

        return $this->mode === 'new' ? 'newest' : 'recommended';
    }

    /** The fixed scope for this page (before shopper-chosen filters). */
    private function scopeQuery(): Builder
    {
        $q = Product::query()->active();

        return match ($this->mode) {
            'category' => $q->whereHas('category', fn ($c) => $c->where('slug', $this->scope)),
            'collection' => $q->whereHas('collections', fn ($c) => $c->where('collections.slug', $this->scope)),
            'new' => $q->where(fn ($w) => $w->where('products.is_new', true)->orWhere('products.published_at', '>=', now()->subDays(45))),
            'deals' => $q->onSale(),
            'search' => app(SearchService::class)->query($this->q),
            default => $q,
        };
    }

    private function filtered(): Builder
    {
        $q = $this->scopeQuery();

        if ($this->categories && $this->mode !== 'category') {
            $q->whereHas('category', fn ($c) => $c->whereIn('slug', $this->categories));
        }
        if ($this->brands) {
            $q->whereHas('brand', fn ($b) => $b->whereIn('slug', $this->brands));
        }
        [$eff, $effBindings] = Product::effectivePriceSql();
        if ($this->min !== '' && is_numeric($this->min)) {
            $q->whereRaw("$eff >= ?", [...$effBindings, (float) $this->min]);
        }
        if ($this->max !== '' && is_numeric($this->max)) {
            $q->whereRaw("$eff <= ?", [...$effBindings, (float) $this->max]);
        }
        if ($this->rating > 0) {
            $q->where('products.rating_avg', '>=', $this->rating);
        }
        if ($this->inStock) {
            $q->inStock();
        }
        if ($this->discount > 0) {
            $list = Product::listPriceSql();
            $q->whereRaw("(($list) - $eff) / ($list) * 100 >= ?", [...$effBindings, $this->discount]);
        }
        if ($this->colors) {
            $q->whereHas('variants', fn ($v) => $v->whereIn('color', $this->colors));
        }
        if ($this->sizes) {
            $q->whereHas('variants', fn ($v) => $v->whereIn('size', $this->sizes));
        }
        foreach ($this->attrs as $pair) {
            [$name, $value] = array_pad(explode('::', $pair, 2), 2, '');
            $q->whereHas('attributes', fn ($a) => $a->where('name', $name)->where('value', $value));
        }

        return $q;
    }

    private function sorted(Builder $q): Builder
    {
        [$eff, $effBindings] = Product::effectivePriceSql();

        return match ($this->effectiveSort()) {
            'newest' => $q->orderByDesc('products.published_at')->orderByDesc('products.id'),
            'price_asc' => $q->orderByRaw("$eff ASC", $effBindings),
            'price_desc' => $q->orderByRaw("$eff DESC", $effBindings),
            'rating' => $q->orderByDesc('products.rating_avg')->orderByDesc('products.rating_count'),
            'popular' => $q->orderByRaw('(products.sales_count * 3 + products.view_count) DESC'),
            default => $this->mode === 'search' && $this->q !== ''
                ? app(SearchService::class)->orderByRelevance($q, $this->q)->orderByDesc('products.rating_avg')
                : $q->orderByDesc('products.is_featured')->orderByDesc('products.rating_avg')->orderByDesc('products.sales_count'),
        };
    }

    private function facets(): array
    {
        $ids = $this->scopeQuery()->pluck('products.id')->all();
        if (! $ids) {
            return ['brands' => collect(), 'colors' => collect(), 'sizes' => collect(), 'categories' => collect(), 'attributes' => collect()];
        }

        return [
            'brands' => Brand::whereHas('products', fn ($p) => $p->whereIn('products.id', $ids))->orderBy('name')->get(['id', 'name', 'slug']),
            'categories' => Category::active()->whereHas('products', fn ($p) => $p->whereIn('products.id', $ids))->get(['id', 'name', 'slug']),
            'colors' => ProductVariant::whereIn('product_id', $ids)->whereNotNull('color')->where('is_active', true)->select('color', 'color_hex')->distinct()->orderBy('color')->get(),
            'sizes' => ProductVariant::whereIn('product_id', $ids)->whereNotNull('size')->where('is_active', true)->distinct()->orderBy('size')->pluck('size'),
            'attributes' => ProductAttribute::whereIn('product_id', $ids)->select('name', 'value')->distinct()->orderBy('name')->orderBy('value')->get()->groupBy('name')->filter(fn ($g) => $g->count() > 1 && $g->count() <= 8)->take(3),
        ];
    }

    private function activeChips(array $facets): array
    {
        $chips = [];
        foreach ($this->categories as $slug) { $chips[] = ['key' => 'categories', 'value' => $slug, 'label' => optional($facets['categories']->firstWhere('slug', $slug))->name ?? $slug]; }
        foreach ($this->brands as $slug) { $chips[] = ['key' => 'brands', 'value' => $slug, 'label' => optional($facets['brands']->firstWhere('slug', $slug))->name ?? $slug]; }
        if ($this->min !== '') { $chips[] = ['key' => 'min', 'value' => null, 'label' => 'From $'.$this->min]; }
        if ($this->max !== '') { $chips[] = ['key' => 'max', 'value' => null, 'label' => 'Up to $'.$this->max]; }
        if ($this->rating) { $chips[] = ['key' => 'rating', 'value' => null, 'label' => $this->rating.'★ & up']; }
        if ($this->discount) { $chips[] = ['key' => 'discount', 'value' => null, 'label' => $this->discount.'%+ off']; }
        if ($this->inStock) { $chips[] = ['key' => 'inStock', 'value' => null, 'label' => 'In stock']; }
        foreach ($this->colors as $c) { $chips[] = ['key' => 'colors', 'value' => $c, 'label' => $c]; }
        foreach ($this->sizes as $s) { $chips[] = ['key' => 'sizes', 'value' => $s, 'label' => 'Size '.$s]; }
        foreach ($this->attrs as $a) { $chips[] = ['key' => 'attrs', 'value' => $a, 'label' => str_replace('::', ': ', $a)]; }

        return $chips;
    }

    public function render()
    {
        $facets = $this->facets();
        $query = $this->sorted($this->filtered()->withEffectivePrice())
            ->with(['images', 'brand', 'variants', 'promotions', 'category']);

        return view('livewire.product-listing', [
            'products' => $query->paginate($this->perPage),
            'facets' => $facets,
            'chips' => $this->activeChips($facets),
            'currentSort' => $this->effectiveSort(),
            'sorts' => [
                'recommended' => $this->mode === 'search' ? 'Best match' : 'Recommended',
                'newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low',
                'rating' => 'Highest rated', 'popular' => 'Most popular',
            ],
        ]);
    }
}
