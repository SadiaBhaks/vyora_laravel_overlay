<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id', 'category_id', 'name', 'slug', 'sku', 'short_description', 'description',
        'price', 'compare_at_price', 'is_featured', 'is_new', 'status', 'features',
        'specifications', 'materials', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'specifications' => 'array',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'published_at' => 'datetime',
            'rating_avg' => 'float',
        ];
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id'); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class)->where('is_active', true)->orderBy('id'); }
    public function allVariants(): HasMany { return $this->hasMany(ProductVariant::class); }
    public function attributes(): HasMany { return $this->hasMany(ProductAttribute::class); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function approvedReviews(): HasMany { return $this->hasMany(Review::class)->where('status', 'approved'); }
    public function collections(): BelongsToMany { return $this->belongsToMany(Collection::class)->withPivot('sort_order'); }
    public function promotions(): BelongsToMany { return $this->belongsToMany(Promotion::class); }

    // ---- Scopes --------------------------------------------------------------------------

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('products.status', 'active')
            ->where(fn ($w) => $w->whereNull('products.published_at')->orWhere('products.published_at', '<=', now()));
    }

    public function scopeInStock(Builder $q): Builder
    {
        return $q->whereHas('variants', fn ($v) => $v->where('stock', '>', 0));
    }

    /** @return array{0:string,1:array} SQL fragment (max active promo %) and its bindings. */
    public static function promoPercentSql(): array
    {
        $now = now()->toDateTimeString();

        return [
            '(SELECT MAX(pr.discount_percent) FROM promotions pr JOIN promotion_product pp ON pp.promotion_id = pr.id'
            .' WHERE pp.product_id = products.id AND pr.is_active = TRUE AND pr.starts_at <= ? AND pr.ends_at > ?)',
            [$now, $now],
        ];
    }

    /** @return array{0:string,1:array} */
    public static function effectivePriceSql(): array
    {
        [$sql, $bindings] = self::promoPercentSql();

        return ["ROUND(products.price * (1 - COALESCE($sql, 0) / 100.0), 2)", $bindings];
    }

    /** SQL for the pre-discount price shown as the "was" price. */
    public static function listPriceSql(): string
    {
        return 'CASE WHEN products.compare_at_price > products.price THEN products.compare_at_price ELSE products.price END';
    }

    public function scopeWithEffectivePrice(Builder $q): Builder
    {
        [$sql, $bindings] = self::effectivePriceSql();

        return $q->select('products.*')->selectRaw("$sql as effective_price", $bindings);
    }

    public function scopeOnSale(Builder $q): Builder
    {
        [$sql, $bindings] = self::promoPercentSql();

        return $q->where(function ($w) use ($sql, $bindings) {
            $w->whereRaw('products.compare_at_price > products.price')
                ->orWhereRaw("COALESCE($sql, 0) > 0", $bindings);
        });
    }

    // ---- Accessors -----------------------------------------------------------------------

    public function activePromotion(): ?Promotion
    {
        $promos = $this->relationLoaded('promotions')
            ? $this->promotions
            : $this->promotions()->get();

        return $promos->filter(fn (Promotion $p) => $p->isRunning())->sortByDesc('discount_percent')->first();
    }

    protected function effectivePrice(): Attribute
    {
        return Attribute::get(function ($value) {
            if ($value !== null) {
                return number_format((float) $value, 2, '.', '');
            }
            $pct = $this->activePromotion()?->discount_percent ?? 0;

            return Money::fromCents((int) round(Money::toCents($this->price) * (100 - $pct) / 100));
        });
    }

    /** The "was" price shown struck through, when there is a genuine reduction. */
    protected function listPrice(): Attribute
    {
        return Attribute::get(function () {
            $effective = (float) $this->effective_price;
            $candidate = $this->compare_at_price !== null ? (float) $this->compare_at_price : (float) $this->price;

            return $candidate > $effective + 0.004 ? number_format($candidate, 2, '.', '') : null;
        });
    }

    protected function discountPercent(): Attribute
    {
        return Attribute::get(function () {
            $list = $this->list_price;

            return $list ? (int) round((1 - ((float) $this->effective_price / (float) $list)) * 100) : 0;
        });
    }

    protected function totalStock(): Attribute
    {
        return Attribute::get(fn () => (int) $this->variants->sum('stock'));
    }

    protected function inStock(): Attribute
    {
        return Attribute::get(fn () => $this->total_stock > 0);
    }

    protected function isNewArrival(): Attribute
    {
        return Attribute::get(fn () => $this->is_new || ($this->published_at && $this->published_at->gt(now()->subDays(21))));
    }

    public function primaryImage(): ?ProductImage { return $this->images->first(); }
    public function secondaryImage(): ?ProductImage { return $this->images->get(1); }

    public function colors(): \Illuminate\Support\Collection
    {
        return $this->variants->filter(fn ($v) => $v->color)->unique('color')->values();
    }

    public function sizes(): \Illuminate\Support\Collection
    {
        return $this->variants->pluck('size')->filter()->unique()->values();
    }

    public function refreshRatingStats(): void
    {
        $stats = $this->approvedReviews()->selectRaw('COUNT(*) as c, AVG(rating) as a')->first();
        $this->forceFill([
            'rating_count' => (int) $stats->c,
            'rating_avg' => $stats->c ? round((float) $stats->a, 2) : 0,
        ])->saveQuietly();
    }
}
