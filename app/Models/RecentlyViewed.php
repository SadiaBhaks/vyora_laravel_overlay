<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentlyViewed extends Model
{
    protected $table = 'recently_viewed_products';

    public $timestamps = false;

    protected $fillable = ['user_id', 'product_id', 'viewed_at'];

    protected function casts(): array { return ['viewed_at' => 'datetime']; }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
