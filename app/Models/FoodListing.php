<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['donor_id', 'title', 'description', 'quantity', 'unit', 'expiry_date', 'pickup_location', 'status', 'food_type', 'image'])]
class FoodListing extends Model
{
    use HasFactory;

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function requests()
    {
        return $this->hasMany(NgoFoodRequest::class, 'food_listing_id');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
