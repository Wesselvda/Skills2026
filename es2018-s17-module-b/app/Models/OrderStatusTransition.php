<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusTransition extends Model
{
    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'note',
    ];

    protected $casts = [
        'old_status' => 'string',
        'new_status' => 'string',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
