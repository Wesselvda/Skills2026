<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'category_key',
        'name',
        'user_ref',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ref', 'user_key');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'category_ref', 'category_key');
    }

    public function bicycles(): HasMany
    {
        return $this->hasMany(Bicycle::class, 'category_ref', 'category_key');
    }

    public function tariffs(): HasMany
    {
        return $this->hasMany(Tariff::class, 'category_ref', 'category_key');
    }
}
