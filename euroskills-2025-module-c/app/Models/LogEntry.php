<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    protected $fillable = [
        'turbineId',
        'timestamp',
        'level',
        'message',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function turbine()
    {
        return $this->belongsTo(Turbine::class, 'turbineId', 'id');
    }
}
