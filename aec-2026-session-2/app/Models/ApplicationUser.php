<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationUser extends Model
{
    protected $fillable = [
        'username',
        'full_name',
        'password',
        'credits',
        'reputation',
    ];

    public function submissions() : HasMany
    {
        return $this->hasMany(Submission::class, 'user_id');
    }

    public function reviews() : HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }
}
