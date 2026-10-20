<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'tagline', 'description', 'art_seed', 'sort_order', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }

    public function scopeActive(Builder $q): Builder { return $q->where('is_active', true)->orderBy('sort_order'); }

    public function artUrl(int $variant = 1): string
    {
        return route('art', ['seed' => ($this->art_seed ?: $this->slug).'-'.$variant, 'c' => $this->slug]);
    }
}
