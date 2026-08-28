<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Build extends Model
{
    protected $fillable = [
        'title',
        'intro',
        'description',
        'active',
        'thumbnail_filename',
        'background_filename',
        'signature_filename',
        'slug',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
