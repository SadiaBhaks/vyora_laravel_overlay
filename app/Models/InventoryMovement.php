<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = ['product_variant_id', 'change', 'stock_after', 'reason', 'reference_type', 'reference_id', 'user_id', 'note'];

    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Apply a stock change and record it. Caller should already hold a row lock inside a transaction. */
    public static function apply(ProductVariant $variant, int $change, string $reason, ?Model $reference = null, ?int $userId = null, ?string $note = null): self
    {
        $variant->stock = $variant->stock + $change;
        $variant->save();

        return self::create([
            'product_variant_id' => $variant->id,
            'change' => $change,
            'stock_after' => $variant->stock,
            'reason' => $reason,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'user_id' => $userId,
            'note' => $note,
        ]);
    }
}
