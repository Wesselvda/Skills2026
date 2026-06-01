<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterCompletion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'chapter_id',
        'credits_earned',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'credits_earned' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
