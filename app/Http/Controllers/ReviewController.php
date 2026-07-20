<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // List all reviews
    public function index(Request $request)
    {
        $reviews = Review::with(['reviewer', 'reviewable'])->latest()->get();
        return response()->json($reviews);
    }

    // Submit a review (can review a FoodListing or a User/NGO/Donor)
    public function store(Request $request)
    {
        $request->validate([
            'reviewable_type' => 'required|string|in:food_listing,user',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $reviewerId = $request->user()->id;
        $type = $request->reviewable_type;
        $id = $request->reviewable_id;

        // Resolve class name
        $modelClass = $type === 'food_listing' ? FoodListing::class : User::class;

        // Check if exists
        $target = $modelClass::find($id);
        if (!$target) {
            return response()->json(['message' => 'Target entity not found'], 404);
        }

        // Prevent self review if User
        if ($type === 'user' && $reviewerId === (int)$id) {
            return response()->json(['message' => 'You cannot review yourself'], 400);
        }

        // Check if already reviewed
        $exists = Review::where('reviewer_id', $reviewerId)
            ->where('reviewable_id', $id)
            ->where('reviewable_type', $modelClass)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already reviewed this entity.'], 400);
        }

        $review = Review::create([
            'reviewer_id' => $reviewerId,
            'reviewable_id' => $id,
            'reviewable_type' => $modelClass,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review->load('reviewer'),
        ], 201);
    }

    // Get reviews for a Food Listing
    public function getFoodListingReviews($id)
    {
        $listing = FoodListing::find($id);
        if (!$listing) {
            return response()->json(['message' => 'Food listing not found'], 404);
        }

        $reviews = Review::with('reviewer')
            ->where('reviewable_id', $id)
            ->where('reviewable_type', FoodListing::class)
            ->latest()
            ->get();

        return response()->json([
            'food_listing' => $listing,
            'reviews' => $reviews,
            'average_rating' => round($reviews->avg('rating'), 1),
            'total_reviews' => $reviews->count(),
        ]);
    }

    // Get reviews for a User (Donor/NGO)
    public function getUserReviews($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $reviews = Review::with('reviewer')
            ->where('reviewable_id', $id)
            ->where('reviewable_type', User::class)
            ->latest()
            ->get();

        return response()->json([
            'user' => $user->makeHidden(['email', 'email_verified_at', 'created_at', 'updated_at']),
            'reviews' => $reviews,
            'average_rating' => round($reviews->avg('rating'), 1),
            'total_reviews' => $reviews->count(),
        ]);
    }

    public function show($id)
    {
        $review = Review::with(['reviewer', 'reviewable'])->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json($review);
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        if ($request->user()->id !== $review->reviewer_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review->update($request->only(['rating', 'comment']));

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->load('reviewer'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        if ($request->user()->id !== $review->reviewer_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }

    public function myReviews(Request $request)
    {
        $reviews = Review::with('reviewable')
            ->where('reviewer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($reviews);
    }
}
