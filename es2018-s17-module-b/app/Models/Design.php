<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Design extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_color_id',
        'name',
        'image_filename',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productColor() : BelongsTo
    {
        return $this->belongsTo(ProductColor::class);
    }
}
