<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a consumer review for a food item.
     */
    public function store(Request $request, Food $food)
    {
        $user = auth()->user();

        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You must be logged in to leave a review.'], 401);
            }
            return back()->with('error', 'You must be logged in to leave a review.');
        }

        // Only Consumers can submit reviews
        if (!$user->isConsumer()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Only consumers can submit ratings and reviews.'], 403);
            }
            return back()->with('error', 'Only consumers can submit ratings and reviews.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'reservation_id' => 'nullable|exists:reservations,id',
        ]);

        // Find associated completed reservation for this consumer and food
        $reservationQuery = Reservation::where('user_id', $user->id)
            ->where('food_id', $food->id)
            ->where('status', Reservation::STATUS_COMPLETED);

        if (!empty($validated['reservation_id'])) {
            $reservationQuery->where('id', $validated['reservation_id']);
        }

        $reservation = $reservationQuery->latest()->first();

        if (!$reservation) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You can only review a food provider after completing a food pickup reservation.'], 422);
            }
            return back()->with('error', 'You can only review a food provider after completing a food pickup reservation.');
        }

        // Check if review already exists for this specific completed reservation
        $existingReview = Review::where('reservation_id', $reservation->id)->first();

        if ($existingReview) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You have already submitted a review for this completed reservation.'], 422);
            }
            return back()->with('error', 'You have already submitted a review for this completed reservation.');
        }

        Review::create([
            'food_id' => $food->id,
            'user_id' => $user->id,
            'reservation_id' => $reservation->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Thank you! Your review and rating have been submitted successfully.',
                'average_rating' => $food->fresh()->average_rating,
                'reviews_count' => $food->fresh()->reviews_count,
            ]);
        }

        return back()->with('success', 'Thank you! Your review and rating have been submitted successfully.');
    }
}
