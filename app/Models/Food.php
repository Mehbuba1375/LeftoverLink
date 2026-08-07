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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0)
                    ->where('expiration_time', '>', now());
    }
}