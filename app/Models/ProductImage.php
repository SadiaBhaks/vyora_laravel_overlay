<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'url', 'art_seed', 'alt', 'sort_order'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    /** Uploaded file > external URL > locally generated artwork (always resolves). */
    public function getSrcAttribute(): string
    {
        if ($this->path) {
            return Storage::disk('public')->url($this->path);
        }
        if ($this->url) {
            return $this->url;
        }

        return route('art', ['seed' => $this->art_seed ?: 'vyora-'.$this->id, 'c' => $this->product?->category?->slug ?? 'lifestyle']);
    }
}
