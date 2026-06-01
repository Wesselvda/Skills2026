<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mentor_name',
        'expertise',
        'experience_level',
        'session_date',
        'duration_minutes',
        'credit_cost',
        'is_available',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'datetime',
            'duration_minutes' => 'integer',
            'credit_cost' => 'integer',
            'is_available' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function sessionBookings(): HasMany
    {
        return $this->hasMany(SessionBooking::class);
    }
}
