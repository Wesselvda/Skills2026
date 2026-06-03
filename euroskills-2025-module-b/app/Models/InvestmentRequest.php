<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvestmentRequest extends Model
{
    protected $fillable = [
        'status',
        'investor_name',
        'investor_email',
        'investor_address',
        'investor_phone',
        'investment_type',
    ];

    public function supports() : HasOne
    {
        return $this->hasOne(Support::class, 'investment_id', 'id');
    }

    public function presentingSponsor() : HasOne
    {
        return $this->hasOne(PresentingSponsor::class, 'investment_id', 'id');
    }

    public function turbine() : HasOne
    {
        return $this->hasOne(Turbine::class, 'investment_id', 'id');
    }
}
