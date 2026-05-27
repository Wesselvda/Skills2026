<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'credit_reward',
        'chapter_order',
        'estimated_minutes',
        'content_type',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'credit_reward' => 'integer',
            'chapter_order' => 'integer',
            'estimated_minutes' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
