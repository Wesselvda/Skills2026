<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    protected $fillable = [
        'tour_date',
        'total_seats'
    ];

    public function bookings() : HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
