<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationUser extends Model
{
    protected $fillable = [
        'email',
        'login_code',
        'role'
    ];

    public function bookings() : HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
