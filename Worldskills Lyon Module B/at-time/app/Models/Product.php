<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'gtin',
        'name_en',
        'name_fr',
        'description_en',
        'description_fr',
        'brand',
        'country_of_origin',
        'gross_weight',
        'net_weight',
        'weight_unit',
        'image_path',
        'is_hidden',
    ];

    protected $casts = [
        'gross_weight' => 'float',
        'net_weight' => 'float',
        'is_hidden' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'gtin';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
