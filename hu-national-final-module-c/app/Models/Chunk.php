<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chunk extends Model
{
    public $timestamps = false;

    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(ContentBlock::class);
    }
}
