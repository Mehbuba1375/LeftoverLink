<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    /**
     * Reservation status constants.
     */
    const STATUS_RESERVED = 'reserved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'food_id',
        'quantity',
        'status',
        'reserved_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'quantity' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * The consumer who made the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The food item that was reserved.
     */
    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * Review associated with this reservation.
     */
    public function review()
    {
        return $this->hasOne(Review::class, 'reservation_id');
    }

    public function isReviewed(): bool
    {
        return $this->review()->exists();
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Scope to only active (reserved) reservations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    /**
     * Scope to filter by a specific status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ─── Status Helpers ──────────────────────────────────────

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * A reservation can only be cancelled if it is currently reserved.
     */
    public function canBeCancelled(): bool
    {
        return $this->isReserved();
    }

    /**
     * A reservation can only be completed if it is currently reserved.
     */
    public function canBeCompleted(): bool
    {
        return $this->isReserved();
    }
}
