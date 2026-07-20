<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    /**
     * Display all food listings
     */
    public function index()
    {
        return Food::all();
    }


    /**
     * Store a new food listing
     */

   


    public function search(Request $request)
    {
        $query = Food::query();

        if ($request->filled('food_name')) {
            $query->where('food_name', 'like', '%' . $request->food_name . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('donation_status')) {
            $query->where('donation_status', $request->donation_status);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        return response()->json($query->get());
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'food_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'expiration_time' => 'required|date',
            'pickup_window' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'donation_status' => 'required|boolean',
        ]);

        $food = Food::create($validated);

        return response()->json([
            'message' => 'Food listing created successfully.',
            'data' => $food
        ], 201);
    }

    /**
     * Display one food listing
     */
    public function show($id)
    {
        $food = Food::find($id);

        if (!$food) {
            return response()->json([
                'message' => 'Food not found.'
            ], 404);
        }

        return response()->json($food);
    }

    /**
     * Update a food listing
     */
    public function update(Request $request, $id)
    {
        $food = Food::find($id);

        if (!$food) {
            return response()->json([
                'message' => 'Food not found.'
            ], 404);
        }

        $validated = $request->validate([
            'food_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'expiration_time' => 'required|date',
            'pickup_window' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'donation_status' => 'required|boolean',
        ]);

        $food->update($validated);

        return response()->json([
            'message' => 'Food updated successfully.',
            'data' => $food
        ]);
    }

    /**
     * Delete a food listing
     */
    public function destroy($id)
    {
        $food = Food::find($id);

        if (!$food) {
            return response()->json([
                'message' => 'Food not found.'
            ], 404);
        }

        $food->delete();

        return response()->json([
            'message' => 'Food deleted successfully.'
        ]);
    }
}