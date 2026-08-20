<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'difficulty',
        'total_chapters',
        'total_credits',
    ];

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order_index');
    }
}
