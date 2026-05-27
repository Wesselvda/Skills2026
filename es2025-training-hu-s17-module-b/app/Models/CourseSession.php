<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSession extends Model
{
    use HasFactory;

    protected $table = 'course_sessions';

    public $timestamps = false;

    protected $fillable = [
        'mentor_id',
        'student_id',
        'session_date',
        'session_time',
        'status',
        'credit_cost',
        'topic',
        'student_rating',
        'student_feedback',
        'mentor_notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'mentor_id' => 'integer',
            'student_id' => 'integer',
            'session_date' => 'date',
            'credit_cost' => 'integer',
            'student_rating' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
