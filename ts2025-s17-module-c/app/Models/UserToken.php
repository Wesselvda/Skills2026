<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'value', 'hash', 'user_id'])]
class UserToken extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    public function user(): BelongsTo
    {
        return $this->belongsTo(ApplicationUser::class, 'user_id');
    }
}
