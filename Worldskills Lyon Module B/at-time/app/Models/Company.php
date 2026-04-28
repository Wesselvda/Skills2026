<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'telephone',
        'email',
        'owner_name',
        'owner_mobile',
        'owner_email',
        'contact_name',
        'contact_mobile',
        'contact_email',
        'is_deactivated',
    ];

    protected $casts = [
        'is_deactivated' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
