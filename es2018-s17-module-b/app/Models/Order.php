<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'status',
        'last_name',
        'first_name',
        'email',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function orderItems() : HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusTransitions() : HasMany
    {
        return $this->hasMany(OrderStatusTransition::class);
    }
}
