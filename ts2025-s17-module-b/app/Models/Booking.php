<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'booking_key',
        'rating',
        'price',
        'percentageOfWear',
        'startedAt',
        'endedAt',
        'bicycle_ref',
        'tariff_ref',
        'user_ref',
        'photos',
    ];

    protected function casts()
    {
        return [
            'rating' => 'integer',
            'price' => 'integer',
            'percentageOfWear' => 'integer',
            'startedAt' => 'datetime',
            'endedAt' => 'datetime',
            'photos' => 'array',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycle_ref', 'bicycle_key');
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class, 'tariff_ref', 'tariff_key');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ref', 'user_key');
    }
}
