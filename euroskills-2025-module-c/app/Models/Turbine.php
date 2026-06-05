<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turbine extends Model
{
    protected $fillable = [
        'id',
        'name',
        'lat',
        'lng',
        'status',
        'rpm',
        'powerMw',
        'yaw',
        'pitch',
        'temperature',
    ];

    public function alerts() : HasMany
    {
        return $this->hasMany([Alert::class], 'turbineId', 'id');
    }

    public function logEntries() : HasMany
    {
        return $this->hasMany([LogEntry::class], 'turbineId', 'id');
    }

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'rpm' => 'float',
        'powerMw' => 'float',
        'yaw' => 'float',
        'pitch' => 'float',
        'temperature' => 'float',
    ];
}
