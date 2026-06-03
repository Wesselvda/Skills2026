<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Support extends Model
{
    protected $fillable = [
        'investment_id',
        'status',
        'amount'
    ];

    public function investment() : BelongsTo
    {
        return $this->belongsTo(InvestmentRequest::class, 'id', 'investment_id');
    }
}
