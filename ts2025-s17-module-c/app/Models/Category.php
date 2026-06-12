<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'name', 'rent_conditions', 'user_id'])]
class Category extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'rent_conditions' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ApplicationUser::class, 'user_id');
    }

    public function bicycles(): HasMany
    {
        return $this->hasMany(Bicycle::class);
    }

    public function tariffs(): HasMany
    {
        return $this->hasMany(Tariff::class);
    }
}
