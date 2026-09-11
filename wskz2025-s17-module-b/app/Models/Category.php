<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['id', 'name'];

    public $incrementing = false;

    protected $keyType = 'string';

    public function adverts()
    {
        return $this->hasMany(Advert::class, 'category_id', 'id');
    }
}
