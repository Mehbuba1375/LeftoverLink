<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['ngo_id', 'food_listing_id', 'quantity_requested', 'message', 'status', 'admin_notes', 'requested_at', 'responded_at', 'contact_name', 'pickup_time', 'address', 'contact_no'])]
class NgoFoodRequest extends Model
{
    use HasFactory;

    protected $casts = [
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function ngo()
    {
        return $this->belongsTo(User::class, 'ngo_id');
    }

    public function foodListing()
    {
        return $this->belongsTo(FoodListing::class, 'food_listing_id');
    }
}
