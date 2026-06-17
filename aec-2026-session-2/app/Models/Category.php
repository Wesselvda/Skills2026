<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'category_id');
    }
}
