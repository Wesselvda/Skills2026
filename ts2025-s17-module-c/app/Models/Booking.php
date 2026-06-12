<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'price_per_min', 'full_price', 'rating', 'userRating', 'percentage_of_wear', 'started_at', 'ended_at', 'bicycle_id', 'tariff_id', 'user_id', 'promo_code_id', 'photos'])]
class Booking extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'price_per_min' => 'integer',
            'full_price' => 'integer',
            'rating' => 'integer',
            'userRating' => 'integer',
            'percentage_of_wear' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'photos' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ApplicationUser::class, 'user_id');
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }
}
