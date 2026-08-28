<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'intro',
        'description',
        'show_in_navigation',
        'image_filename',
        'slug',
        'tag',
    ];

    protected function casts(): array
    {
        return [
            'show_in_navigation' => 'boolean',
        ];
    }
}
