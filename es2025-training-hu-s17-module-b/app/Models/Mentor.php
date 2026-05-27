<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mentor extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'expertise_areas',
        'hourly_credit_rate',
        'bio',
        'years_experience',
        'availability_status',
        'approval_status',
        'approval_date',
        'total_sessions_completed',
        'average_rating',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'hourly_credit_rate' => 'integer',
            'years_experience' => 'integer',
            'approval_date' => 'datetime',
            'total_sessions_completed' => 'integer',
            'average_rating' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CourseSession::class, 'mentor_id');
    }
}
