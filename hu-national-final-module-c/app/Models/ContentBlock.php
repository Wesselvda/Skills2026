<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentBlock extends Model
{
    public $timestamps = false;

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(Chunk::class)->orderBy('order_index');
    }
}
