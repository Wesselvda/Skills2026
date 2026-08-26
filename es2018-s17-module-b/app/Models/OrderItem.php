<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_color_id',
        'design_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productColor() : BelongsTo
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function design() : BelongsTo
    {
        return $this->belongsTo(Design::class);
    }
}
