<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'status', 'category_id', 'user_id'])]
class Application extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';
}
