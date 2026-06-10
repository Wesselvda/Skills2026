<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationUser extends Model
{
    protected $fillable = [
        'username',
        'password',
        'role',
        'token'
    ];
}
