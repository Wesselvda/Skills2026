<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsHtmlString;
use Illuminate\Database\Eloquent\Model;

class MockEmail extends Model
{
    protected $fillable = [
        'recipient',
        'subject',
        'body'
    ];

    protected $casts = [
        'body' => AsHtmlString::class
    ];
}
