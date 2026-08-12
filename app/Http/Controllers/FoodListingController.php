<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;
use Illuminate\Http\Request;

class FoodListingController extends Controller
{
    public function index(Request $request)
    {
        $query = FoodListing::with('donor');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to only available listings
            $query->where('status', 'available');
        }

        if ($request->has('food_type')) {
            $query->where('food_type', $request->food_type);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        // Donors only
        if ($request->user()->role !== 'donor' && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only donors can list food.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:20',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'pickup_location' => 'required|string',
            'food_type' => 'nullable|string',
            'image' => 'nullable|string', // Base64 or URL
        ]);

        $listing = FoodListing::create([
            'donor_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'expiry_date' => $request->expiry_date,
            'pickup_location' => $request->pickup_location,
            'food_type' => $request->food_type,
            'image' => $request->image,
            'status' => 'available',
        ]);

        return response()->json([
            'message' => 'Food listing created successfully',
            'food_listing' => $listing->load('donor'),
        ], 201);
    }

    public function show($id)
    {
        $listing = FoodListing::with(['donor', 'reviews.reviewer'])->find($id);

        if (!$listing) {
            return response()->json(['message' => 'Food listing not found'], 404);
        }

        return response()->json($listing);
    }

    public function update(Request $request, $id)
    {
        $listing = FoodListing::find($id);

        if (!$listing) {
            return response()->json(['message' => 'Food listing not found'], 404);
        }

        // Only owner donor or admin can update
        if ($request->user()->id !== $listing->donor_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'sometimes|required|integer|min:1',
            'unit' => 'sometimes|required|string|max:20',
            'expiry_date' => 'nullable|date',
            'pickup_location' => 'sometimes|required|string',
            'food_type' => 'nullable|string',
            'status' => 'sometimes|required|string|in:available,requested,fulfilled,expired',
            'image' => 'nullable|string',
        ]);

        $listing->update($request->all());

        return response()->json([
            'message' => 'Food listing updated successfully',
            'food_listing' => $listing->load('donor'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $listing = FoodListing::find($id);

        if (!$listing) {
            return response()->json(['message' => 'Food listing not found'], 404);
        }

        if ($request->user()->id !== $listing->donor_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $listing->delete();

        return response()->json([
            'message' => 'Food listing deleted successfully',
        ]);
    }
}
