<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    // Explicitly tell Laravel which table to use
    protected $table = 'foods';

    protected $fillable = [
        'food_name',
        'category',
        'quantity',
        'price',
        'expiration_time',
        'pickup_window',
        'image',
        'donation_status',
    ];
}