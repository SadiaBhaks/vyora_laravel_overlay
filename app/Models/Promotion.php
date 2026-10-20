<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'discount_percent', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function products(): BelongsToMany { return $this->belongsToMany(Product::class); }

    public function scopeRunning(Builder $q): Builder
    {
        return $q->where('is_active', true)->where('starts_at', '<=', now())->where('ends_at', '>', now());
    }

    public function isRunning(): bool
    {
        return $this->is_active && $this->starts_at->lte(now()) && $this->ends_at->gt(now());
    }
}
