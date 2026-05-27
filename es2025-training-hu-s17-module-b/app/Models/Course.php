<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'total_credits',
        'difficulty_level',
        'estimated_hours',
        'status',
        'category',
        'created_date',
        'instructor_name',
    ];

    protected function casts(): array
    {
        return [
            'total_credits' => 'integer',
            'estimated_hours' => 'integer',
            'created_date' => 'datetime',
        ];
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
