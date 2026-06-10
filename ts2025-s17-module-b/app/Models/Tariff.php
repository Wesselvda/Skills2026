<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tariff extends Model
{
    protected $fillable = [
        'tariff_key',
        'name',
        'type',
        'basePrice',
        'minPrice',
        'maxPrice',
        'category_ref',
        'archived',
    ];

    protected function casts()
    {
        return [
            'basePrice' => 'integer',
            'minPrice' => 'integer',
            'maxPrice' => 'integer',
            'archived' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_ref', 'category_key');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tariff_ref', 'tariff_key');
    }
}
