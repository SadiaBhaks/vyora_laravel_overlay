<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const TIMELINE = [
        'pending' => 'Order placed',
        'processing' => 'Processing',
        'packed' => 'Packed',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'is_demo' => 'boolean',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
            'restocked_at' => 'datetime',
            'estimated_delivery_from' => 'date',
            'estimated_delivery_to' => 'date',
        ];
    }

    public function getRouteKeyName(): string { return 'number'; }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class)->latest('id'); }
    public function histories(): HasMany { return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at')->orderBy('id'); }

    public function isPaid(): bool { return $this->payment_status === 'paid'; }

    public function money(string $field): string { return Money::format((int) $this->{$field.'_cents'}); }

    public function statusLabel(): string
    {
        return $this->status === 'cancelled' ? 'Cancelled' : (self::TIMELINE[$this->status] ?? ucfirst($this->status));
    }

    public function paymentLabel(): string
    {
        return match (true) {
            $this->payment_status === 'paid' && $this->is_demo => 'Demo payment (no real charge was made)',
            $this->payment_status === 'paid' => 'Paid',
            $this->payment_status === 'failed' => 'Payment failed',
            $this->payment_status === 'cancelled' => 'Cancelled',
            default => 'Awaiting payment',
        };
    }
}
