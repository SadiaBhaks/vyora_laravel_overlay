<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_STAFF = 'staff';
    public const ROLE_ADMIN = 'admin';

    // `role` is deliberately NOT mass assignable: nobody can self-promote via forms.
    protected $fillable = ['name', 'email', 'password', 'phone', 'marketing_opt_in'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'marketing_opt_in' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [self::ROLE_STAFF, self::ROLE_ADMIN], true);
    }

    public function addresses(): HasMany { return $this->hasMany(Address::class)->orderByDesc('is_default')->orderBy('id'); }
    public function orders(): HasMany { return $this->hasMany(Order::class)->latest('placed_at'); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function cart(): HasOne { return $this->hasOne(Cart::class); }
    public function preferences(): HasOne { return $this->hasOne(UserPreference::class); }
    public function wishlistItems(): HasMany { return $this->hasMany(WishlistItem::class); }

    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlist_items')->withTimestamps();
    }

    public function recentlyViewed(): HasMany
    {
        return $this->hasMany(RecentlyViewed::class)->orderByDesc('viewed_at');
    }
}
