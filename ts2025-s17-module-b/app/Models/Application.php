<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'application_key',
        'category_ref',
        'user_ref',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_ref', 'category_key');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ref', 'user_key');
    }
}
