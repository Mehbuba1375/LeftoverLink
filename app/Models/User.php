<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'profile_photo',
        'address',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function foods()
    {
        return $this->hasMany(Food::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function foodRequests()
    {
        return $this->hasMany(FoodRequest::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteFoods()
    {
        return $this->belongsToMany(Food::class, 'favorites', 'user_id', 'food_id')->withTimestamps();
    }

    public function reviewsWritten()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function reviewsReceived()
    {
        return $this->hasManyThrough(Review::class, Food::class, 'user_id', 'food_id', 'id', 'id');
    }

    public function getAverageRatingAttribute()
    {
        $avg = $this->reviewsReceived()->avg('rating');
        return $avg ? round($avg, 1) : 0.0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviewsReceived()->count();
    }

    public function isConsumer(): bool
    {
        return $this->role === 'consumer';
    }

    public function isProvider(): bool
    {
        return $this->role === 'food_provider';
    }

    public function isNgo(): bool
    {
        return $this->role === 'ngo';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
