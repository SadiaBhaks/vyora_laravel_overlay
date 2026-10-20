<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'status', 'note', 'user_id', 'created_at'];

    protected function casts(): array { return ['created_at' => 'datetime']; }
}
