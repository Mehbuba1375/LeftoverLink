<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodRequest extends Model
{
    use HasFactory;

    protected $table = 'food_requests';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'food_id',
        'quantity',
        'status',
        'notes',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * The NGO user who created the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The donated food item requested.
     */
    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    // ─── Helper Methods ──────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
