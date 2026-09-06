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
        'pickup_start_time',
        'pickup_end_time',
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
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function foodRequests()
    {
        return $this->hasMany(FoodRequest::class);
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

    public function getPickupWindowAttribute($value)
    {
        if ($this->pickup_start_time && $this->pickup_end_time) {
            $start = \Carbon\Carbon::parse($this->pickup_start_time)->format('g:i A');
            $end = \Carbon\Carbon::parse($this->pickup_end_time)->format('g:i A');
            return "{$start} – {$end}";
        }
        return $value ?? 'Flexible';
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/default-food.png');
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0)
                    ->where('expiration_time', '>', now());
    }
}