<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentingSponsor extends Model
{
    protected $fillable = [
        'investment_id',
        'status',
        'logo_filename'
    ];

    public function investment() : BelongsTo
    {
        return $this->belongsTo(InvestmentRequest::class, 'id', 'investment_id');
    }
}
