<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'tour_id',
        'user_id',
        'name',
        'address',
        'phone',
        'seats'
    ];

    public function tour() : BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
