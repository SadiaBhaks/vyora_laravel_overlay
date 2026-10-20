<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'color', 'color_hex', 'size', 'price_override', 'stock', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean', 'stock' => 'integer']; }

    public function product(): BelongsTo { return $this->belongsTo(Product::class)->withTrashed(); }
    public function movements(): HasMany { return $this->hasMany(InventoryMovement::class); }

    public function label(): string
    {
        return collect([$this->color, $this->size])->filter()->implode(' / ') ?: 'Standard';
    }
}
