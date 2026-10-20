<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    protected $fillable = ['name', 'slug', 'tagline', 'description', 'art_seed', 'sort_order', 'is_featured', 'is_active'];

    protected function casts(): array { return ['is_featured' => 'boolean', 'is_active' => 'boolean']; }

    public function getRouteKeyName(): string { return 'slug'; }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function scopeActive(Builder $q): Builder { return $q->where('is_active', true)->orderBy('sort_order'); }

    public function artUrl(int $variant = 1, string $kind = 'lifestyle'): string
    {
        return route('art', ['seed' => ($this->art_seed ?: $this->slug).'-'.$variant, 'c' => $kind, 'w' => 'wide']);
    }
}
