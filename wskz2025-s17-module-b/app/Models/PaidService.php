<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaidService extends Model
{
    protected $fillable = [
        'type',
        'activated_at',
        'validity_days',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'is_enabled' => 'boolean',
        ];
    }

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }

    public function getExpiresAtAttribute()
    {
        return $this->activated_at?->copy()->addDays($this->validity_days);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }
}
