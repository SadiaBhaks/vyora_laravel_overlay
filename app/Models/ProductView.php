<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    public $timestamps = false;

    protected $fillable = ['product_id', 'visitor_hash', 'is_demo', 'viewed_at'];

    protected function casts(): array { return ['viewed_at' => 'datetime', 'is_demo' => 'boolean']; }
}
