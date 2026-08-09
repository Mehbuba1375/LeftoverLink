<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['reviewer_id', 'reviewable_id', 'reviewable_type', 'rating', 'comment', 'packing_feedback', 'food_feedback', 'time_feedback'])]
class Review extends Model
{
    use HasFactory;

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewable()
    {
        return $this->morphTo();
    }
}
