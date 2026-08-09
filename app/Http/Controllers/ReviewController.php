<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a consumer review for a food item.
     */
    public function store(Request $request, Food $food)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id() ?? $request->input('user_id');

        if (!$userId) {
            return back()->with('error', 'You must be logged in to leave a review.');
        }

        Review::create([
            'food_id' => $food->id,
            'user_id' => $userId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Review submitted successfully!',
                'average_rating' => $food->fresh()->average_rating,
                'reviews_count' => $food->fresh()->reviews_count,
            ]);
        }

        return back()->with('success', 'Thank you for your rating and review!');
    }
}
