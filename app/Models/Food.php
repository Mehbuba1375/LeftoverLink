<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = [
        'user_id',
        'food_name',
        'description',
        'category',
        'quantity',
        'price',
        'expiration_time',
        'pickup_window',
        'image',
        'donation_status',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'expiration_time' => 'datetime',
        'donation_status' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'average_rating',
        'reviews_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function ngoWebRequests()
    {
        return $this->hasMany(NgoWebRequest::class, 'food_id');
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'food_id', 'user_id')->withTimestamps();
    }

    public function getAverageRatingAttribute()
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round($avg, 1) : 0.0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0)
                    ->where('expiration_time', '>', now());
    }
}