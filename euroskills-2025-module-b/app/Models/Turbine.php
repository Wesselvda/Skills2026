<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turbine extends Model
{
    protected $fillable = [
        'investment_id',
        'name',
        'status',
        'logo_filename',
        'displayed_text'
    ];

    public function investment() : BelongsTo
    {
        return $this->belongsTo(InvestmentRequest::class, 'id', 'investment_id');
    }
}
