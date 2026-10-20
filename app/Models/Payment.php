<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array { return ['is_demo' => 'boolean']; }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
