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
        'payment_status',
        'payment_id',
        'preferred_pickup_date',
        'preferred_pickup_time',
        'approved_pickup_date',
        'approved_pickup_time',
        'pickup_schedule_status',
        'reserved_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'preferred_pickup_date' => 'date',
        'approved_pickup_date' => 'date',
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
     * Payment record for this reservation.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
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

    // ─── Pickup Schedule Helpers ─────────────────────────────

    public function isSchedulePending(): bool
    {
        return $this->pickup_schedule_status === 'pending' || empty($this->pickup_schedule_status);
    }

    public function isScheduleApproved(): bool
    {
        return $this->pickup_schedule_status === 'approved';
    }

    public function isScheduleAdjusted(): bool
    {
        return $this->pickup_schedule_status === 'adjusted';
    }

    public function getFormattedPreferredScheduleAttribute(): ?string
    {
        if (!$this->preferred_pickup_date) {
            return null;
        }
        $dateStr = $this->preferred_pickup_date->format('M d, Y');
        $timeStr = $this->preferred_pickup_time ? \Carbon\Carbon::parse($this->preferred_pickup_time)->format('g:i A') : '';
        return trim("{$dateStr} {$timeStr}");
    }

    public function getFormattedApprovedScheduleAttribute(): ?string
    {
        if (!$this->approved_pickup_date) {
            return null;
        }
        $dateStr = $this->approved_pickup_date->format('M d, Y');
        $timeStr = $this->approved_pickup_time ? \Carbon\Carbon::parse($this->approved_pickup_time)->format('g:i A') : '';
        return trim("{$dateStr} {$timeStr}");
    }
}
