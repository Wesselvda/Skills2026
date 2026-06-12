<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'status', 'category_id', 'user_id'])]
class Application extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ApplicationUser::class, 'user_id');
    }
}
