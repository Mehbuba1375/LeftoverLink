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

    // ─── Payment Status Helpers (Online Payment System) ──────
    //
    // These are purely additive. The `status` column and every helper above
    // it are left untouched, so reviews, pickup reminders and the
    // sustainability dashboard keep behaving exactly as before. "Ready for
    // Pickup" is derived from `payment_status`, not stored as a new status.

    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_CANCELLED = 'cancelled';

    /**
     * True when the reserved item is a priced (non-donation) listing and
     * therefore needs an online payment.
     */
    public function requiresPayment(): bool
    {
        return $this->food
            && !$this->food->donation_status
            && (float) $this->food->price > 0;
    }

    /**
     * Total payable amount (unit price x reserved quantity).
     */
    public function getPayableAmountAttribute(): float
    {
        if (!$this->requiresPayment()) {
            return 0.00;
        }

        return round((float) $this->food->price * $this->quantity, 2);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isPaymentPending(): bool
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }

    public function isPaymentFailed(): bool
    {
        return in_array($this->payment_status, [self::PAYMENT_FAILED, self::PAYMENT_CANCELLED], true);
    }

    /**
     * A paid reservation that has not been picked up yet is Ready for Pickup.
     */
    public function isReadyForPickup(): bool
    {
        return $this->isReserved() && $this->isPaid();
    }

    /**
     * The consumer still owes payment (first attempt or a retry).
     */
    public function awaitingPayment(): bool
    {
        return $this->isReserved() && $this->requiresPayment() && !$this->isPaid();
    }

    /**
     * Human readable status shown to the consumer.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->isCancelled()) {
            return 'Cancelled';
        }

        if ($this->isCompleted()) {
            return 'Completed';
        }

        if ($this->isReadyForPickup()) {
            return 'Paid — Ready for Pickup';
        }

        if ($this->awaitingPayment()) {
            return match ($this->payment_status) {
                self::PAYMENT_PENDING => 'Payment Pending',
                self::PAYMENT_FAILED => 'Payment Failed',
                self::PAYMENT_CANCELLED => 'Payment Cancelled',
                default => 'Awaiting Payment',
            };
        }

        return 'Reserved';
    }

    /**
     * Tailwind badge classes matching the display status.
     */
    public function getDisplayStatusClassAttribute(): string
    {
        if ($this->isCancelled()) {
            return 'bg-red-100 text-red-700';
        }

        if ($this->isCompleted()) {
            return 'bg-[#2E7D32]/10 text-[#2E7D32]';
        }

        if ($this->isReadyForPickup()) {
            return 'bg-emerald-100 text-emerald-800';
        }

        if ($this->isPaymentFailed()) {
            return 'bg-red-100 text-red-700';
        }

        if ($this->awaitingPayment()) {
            return 'bg-amber-100 text-amber-800';
        }

        return 'bg-blue-100 text-blue-700';
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
