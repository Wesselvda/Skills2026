<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'code', 'discount_percent', 'free_minutes', 'expires_at', 'category_id'])]
class PromoCode extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer',
            'free_minutes' => 'integer',
            'expires_at' => 'datetime',
        ];
    }
}
