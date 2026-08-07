<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\User;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Display Consumer Marketplace with search & multi-filtering.
     */
    public function index(Request $request)
    {
        $query = Food::available()->with(['user', 'reviews']);

        $this->applyFilters($query, $request);

        $foods = $query->paginate(12)->withQueryString();

        $categories = [
            'Prepared Meals',
            'Bakery & Pastries',
            'Fresh Produce',
            'Dairy & Eggs',
            'Beverages',
            'Groceries & Snacks',
            'Other'
        ];

        $providers = User::where('role', 'food_provider')->select('id', 'name')->get();
        $isDonationPage = false;

        return view('marketplace.index', compact('foods', 'categories', 'providers', 'isDonationPage'));
    }

    /**
     * Display Available Donations page (Restricted strictly to donated listings).
     */
    public function donations(Request $request)
    {
        $request->merge(['is_donation_page' => '1', 'type' => 'donated']);

        $query = Food::available()->where('donation_status', true)->with(['user', 'reviews']);

        $this->applyFilters($query, $request);

        $foods = $query->paginate(12)->withQueryString();

        $categories = [
            'Prepared Meals',
            'Bakery & Pastries',
            'Fresh Produce',
            'Dairy & Eggs',
            'Beverages',
            'Groceries & Snacks',
            'Other'
        ];

        $providers = User::where('role', 'food_provider')->select('id', 'name')->get();
        $isDonationPage = true;

        return view('marketplace.index', compact('foods', 'categories', 'providers', 'isDonationPage'));
    }

    /**
     * Live search API for real-time dynamic filtering.
     */
    public function searchApi(Request $request)
    {
        $query = Food::available()->with(['user:id,name,phone,address', 'reviews']);

        $this->applyFilters($query, $request);

        $foods = $query->get()->map(function ($food) {
            return [
                'id' => $food->id,
                'food_name' => $food->food_name,
                'category' => $food->category,
                'quantity' => $food->quantity,
                'price' => number_format($food->price, 2),
                'expiration_time' => $food->expiration_time ? $food->expiration_time->format('M d, Y h:i A') : null,
                'pickup_window' => $food->pickup_window,
                'donation_status' => (bool)$food->donation_status,
                'image_url' => $food->image ? asset('storage/' . $food->image) : asset('images/default-food.png'),
                'provider_name' => $food->user ? $food->user->name : 'Food Provider',
                'provider_id' => $food->user_id,
                'average_rating' => $food->average_rating,
                'reviews_count' => $food->reviews_count,
                'latitude' => $food->latitude,
                'longitude' => $food->longitude,
            ];
        });

        return response()->json([
            'count' => $foods->count(),
            'data' => $foods
        ]);
    }

    /**
     * Private helper to apply search, filters, and sorting to query.
     */
    private function applyFilters($query, Request $request)
    {
        // Enforce donation_status = true if on donations page or type = donated
        if ($request->boolean('is_donation_page') || $request->type === 'donated') {
            $query->where('donation_status', true);
        } elseif ($request->filled('type') && $request->type === 'discounted') {
            $query->where('donation_status', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('food_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('provider_id') && $request->provider_id !== 'all') {
            $query->where('user_id', $request->provider_id);
        }

        // Apply Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'expiring_soon' => $query->orderBy('expiration_time', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }
}
