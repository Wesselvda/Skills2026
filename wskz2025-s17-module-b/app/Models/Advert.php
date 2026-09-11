<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advert extends Model
{
    protected $fillable = [
        'title',
        'text',
        'status',
        'price',
        'views_count',
        'category_id',
        'author_email',
        'photos',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_email', 'email');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function paidServices()
    {
        return $this->hasMany(PaidService::class);
    }
}
