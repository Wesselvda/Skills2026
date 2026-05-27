<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_date',
        'progress_percentage',
        'completed_chapters',
        'total_chapters',
        'completion_date',
        'status',
        'last_activity',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'course_id' => 'integer',
            'enrollment_date' => 'datetime',
            'progress_percentage' => 'integer',
            'completed_chapters' => 'integer',
            'total_chapters' => 'integer',
            'completion_date' => 'datetime',
            'last_activity' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
