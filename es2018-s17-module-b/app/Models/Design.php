<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Design extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'image_filename',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
