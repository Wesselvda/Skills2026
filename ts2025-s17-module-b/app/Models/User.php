<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_key',
        'name',
        'email',
        'phone',
        'password',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'user_ref', 'user_key');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'user_ref', 'user_key');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_ref', 'user_key');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
