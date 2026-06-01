<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionBooking extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'mentor_session_id',
        'status',
        'credits_paid',
        'booked_at',
    ];

    protected function casts(): array
    {
        return [
            'credits_paid' => 'integer',
            'booked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentorSession(): BelongsTo
    {
        return $this->belongsTo(MentorSession::class);
    }
}
