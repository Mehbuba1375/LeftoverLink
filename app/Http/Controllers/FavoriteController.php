<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Food;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display logged in consumer's favorite food listings.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $favoriteFoods = $user->favoriteFoods()
            ->with(['user', 'reviews'])
            ->latest('favorites.created_at')
            ->get();

        return view('favorites.index', compact('favoriteFoods'));
    }

    /**
     * Toggle favorite status for a food listing.
     */
    public function toggle(Request $request, Food $food)
    {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Please log in to save favorites.', 'redirect' => route('login')], 401);
            }
            return redirect()->route('login');
        }

        $userId = auth()->id();
        $favorite = Favorite::where('user_id', $userId)->where('food_id', $food->id)->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorited = false;
            $message = 'Removed from Favorites.';
        } else {
            Favorite::create([
                'user_id' => $userId,
                'food_id' => $food->id,
            ]);
            $isFavorited = true;
            $message = 'Added to Favorites.';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'is_favorited' => $isFavorited,
                'message' => $message,
                'food_id' => $food->id,
            ]);
        }

        return back()->with('success', $message);
    }
}
