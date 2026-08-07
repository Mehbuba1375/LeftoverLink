<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    /**
     * Display all available food listings.
     */
    public function index()
    {
        return response()->json(Food::available()->with('user:id,name,phone')->latest()->get());
    }

    /**
     * Search endpoint for food items.
     */
    public function search(Request $request)
    {
        $query = Food::available()->with('user:id,name,phone');

        if ($request->filled('food_name')) {
            $query->where('food_name', 'like', '%' . $request->food_name . '%');
        }

        if ($request->filled('provider_name')) {
            $search = $request->provider_name;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('donation_status')) {
            $query->where('donation_status', filter_var($request->donation_status, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        return response()->json($query->get());
    }

    /**
     * Store a new food listing.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'food_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'expiration_time' => 'required|date|after:now',
            'pickup_window' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'donation_status' => 'required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $validated['user_id'] = auth()->id() ?? $request->input('user_id');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('food-images', 'public');
            $validated['image'] = $path;
        }

        if ($validated['donation_status']) {
            $validated['price'] = 0;
        }

        $food = Food::create($validated);

        return response()->json([
            'message' => 'Food listing created successfully.',
            'data' => $food->load('user:id,name')
        ], 201);
    }

    /**
     * Display single food item.
     */
    public function show($id)
    {
        $food = Food::with('user:id,name,phone,address')->find($id);

        if (!$food) {
            return response()->json(['message' => 'Food not found.'], 404);
        }

        return response()->json($food);
    }

    /**
     * Update food item.
     */
    public function update(Request $request, $id)
    {
        $food = Food::find($id);

        if (!$food) {
            return response()->json(['message' => 'Food not found.'], 404);
        }

        if (auth()->check() && $food->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'food_name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer|min:0',
            'price' => 'sometimes|required|numeric|min:0',
            'expiration_time' => 'sometimes|required|date',
            'pickup_window' => 'sometimes|required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'donation_status' => 'sometimes|required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($request->hasFile('image')) {
            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }
            $path = $request->file('image')->store('food-images', 'public');
            $validated['image'] = $path;
        }

        if (isset($validated['donation_status']) && $validated['donation_status']) {
            $validated['price'] = 0;
        }

        $food->update($validated);

        return response()->json([
            'message' => 'Food updated successfully.',
            'data' => $food
        ]);
    }

    /**
     * Delete food item.
     */
    public function destroy($id)
    {
        $food = Food::find($id);

        if (!$food) {
            return response()->json(['message' => 'Food not found.'], 404);
        }

        if (auth()->check() && $food->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($food->image && Storage::disk('public')->exists($food->image)) {
            Storage::disk('public')->delete($food->image);
        }

        $food->delete();

        return response()->json(['message' => 'Food deleted successfully.']);
    }
}