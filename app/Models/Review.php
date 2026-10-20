<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'user_id', 'order_id', 'rating', 'title', 'body', 'is_verified', 'is_demo', 'status'];

    protected function casts(): array { return ['is_verified' => 'boolean', 'is_demo' => 'boolean']; }

    protected static function booted(): void
    {
        $refresh = fn (Review $r) => $r->product?->refreshRatingStats();
        static::saved($refresh);
        static::deleted($refresh);
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class)->withTrashed(); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
