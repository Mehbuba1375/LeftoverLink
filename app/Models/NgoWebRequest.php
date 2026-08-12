<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NgoWebRequest extends Model
{
    use HasFactory;

    protected $table = 'ngo_web_requests';

    protected $fillable = [
        'ngo_id',
        'food_id',
        'quantity_requested',
        'message',
        'status',
        'contact_name',
        'pickup_time',
        'address',
        'contact_no',
        'requested_at',
        'responded_at',
        'admin_notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FULFILLED = 'fulfilled';

    // Relationships
    public function ngo()
    {
        return $this->belongsTo(User::class, 'ngo_id');
    }

    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    // Status Helpers
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

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }
}
