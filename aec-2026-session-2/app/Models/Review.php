<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'submission_id',
        'is_valid',
        'is_positive',
        'comment',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(ApplicationUser::class, 'user_id');
    }

    public function submission() : BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}
