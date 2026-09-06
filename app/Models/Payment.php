<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'PENDING';
    const STATUS_VALIDATED = 'VALIDATED';
    const STATUS_FAILED = 'FAILED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_REFUNDED = 'REFUNDED';

    protected $fillable = [
        'user_id',
        'food_id',
        'reservation_id',
        'tran_id',
        'val_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'card_type',
        'card_no',
        'bank_tran_id',
        'pay_time',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'pay_time' => 'datetime',
        'raw_response' => 'array',
    ];

    /**
     * Relationship: The consumer who made the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: The food item purchased.
     */
    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * Relationship: The associated reservation.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function isValidated(): bool
    {
        return $this->status === self::STATUS_VALIDATED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
