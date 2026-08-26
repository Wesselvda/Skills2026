<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationItem extends Model
{
    protected $fillable = [
        'name',
        'link',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}
