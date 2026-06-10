<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bicycle extends Model
{
    protected $fillable = [
        'bicycle_key',
        'name',
        'slug',
        'description',
        'locationX',
        'locationY',
        'pathToImage',
        'category_ref',
    ];

    protected function casts()
    {
        return [
            'locationX' => 'decimal:2',
            'locationY' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_ref', 'category_key');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'bicycle_ref', 'bicycle_key');
    }
}
