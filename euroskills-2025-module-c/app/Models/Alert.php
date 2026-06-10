<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'turbineId',
        'type',
        'status',
        'acknowledged'
    ];

    public function turbine() : BelongsTo
    {
        return $this->belongsTo([Turbine::class], 'id', 'turbineId');
    }
}
