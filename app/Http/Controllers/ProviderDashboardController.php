<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProviderDashboardController extends Controller
{
    /**
     * Display Food Provider Management Dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $listings = Food::where('user_id', $user->id)
            ->latest()
            ->get();

        $ngoRequests = \App\Models\FoodRequest::whereHas('food', fn($q) => $q->where('user_id', $user->id))
            ->with(['food', 'user'])
            ->latest()
            ->get();

        $incomingReservations = \App\Models\Reservation::whereHas('food', fn($q) => $q->where('user_id', $user->id))
            ->with(['food', 'user'])
            ->latest('reserved_at')
            ->get();

        $stats = [
            'total' => $listings->count(),
            'active' => $listings->filter(fn($f) => $f->quantity > 0 && $f->expiration_time > now())->count(),
            'expired' => $listings->filter(fn($f) => $f->expiration_time <= now())->count(),
            'out_of_stock' => $listings->filter(fn($f) => $f->quantity == 0)->count(),
            'ngo_requests_total' => $ngoRequests->count(),
            'ngo_requests_pending' => $ngoRequests->where('status', \App\Models\FoodRequest::STATUS_PENDING)->count(),
            'reservations_total' => $incomingReservations->count(),
            'reservations_active' => $incomingReservations->where('status', \App\Models\Reservation::STATUS_RESERVED)->count(),
        ];

        return view('provider.dashboard', compact('listings', 'stats', 'ngoRequests', 'incomingReservations'));
    }

    /**
     * Show form to create a new food listing.
     */
    public function create()
    {
        return view('provider.create');
    }

    /**
     * Store new food listing from Provider Dashboard.
     */
    public function store(Request $request)
    {
        // Sanitize donation_status boolean input
        $request->merge([
            'donation_status' => filter_var($request->input('donation_status'), FILTER_VALIDATE_BOOLEAN)
        ]);

        $validated = $request->validate([
            'food_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'expiration_time' => 'required|date',
            'pickup_start_time' => 'required|date_format:H:i',
            'pickup_end_time' => 'required|date_format:H:i|after:pickup_start_time',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'donation_status' => 'required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'pickup_end_time.after' => 'The last pickup time must be later than the starting pickup time.',
        ]);

        $startFormatted = \Carbon\Carbon::parse($validated['pickup_start_time'])->format('g:i A');
        $endFormatted = \Carbon\Carbon::parse($validated['pickup_end_time'])->format('g:i A');
        $validated['pickup_window'] = "{$startFormatted} – {$endFormatted}";

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('food-images', 'public');
            $validated['image'] = $path;
        }

        // If donation, set price to 0
        if ($validated['donation_status']) {
            $validated['price'] = 0;
        }

        Food::create($validated);

        return redirect()->route('provider.dashboard')
            ->with('success', 'Food listing published successfully!');
    }

    /**
     * Update food listing.
     */
    public function update(Request $request, Food $food)
    {
        if ($food->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->merge([
            'donation_status' => filter_var($request->input('donation_status'), FILTER_VALIDATE_BOOLEAN)
        ]);

        $validated = $request->validate([
            'food_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'expiration_time' => 'required|date',
            'pickup_start_time' => 'required|date_format:H:i',
            'pickup_end_time' => 'required|date_format:H:i|after:pickup_start_time',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'donation_status' => 'required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'pickup_end_time.after' => 'The last pickup time must be later than the starting pickup time.',
        ]);

        $startFormatted = \Carbon\Carbon::parse($validated['pickup_start_time'])->format('g:i A');
        $endFormatted = \Carbon\Carbon::parse($validated['pickup_end_time'])->format('g:i A');
        $validated['pickup_window'] = "{$startFormatted} – {$endFormatted}";

        if ($request->hasFile('image')) {
            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }
            $path = $request->file('image')->store('food-images', 'public');
            $validated['image'] = $path;
        }

        if ($validated['donation_status']) {
            $validated['price'] = 0;
        }

        $food->update($validated);

        return redirect()->route('provider.dashboard')
            ->with('success', 'Food listing updated successfully!');
    }

    /**
     * Delete food listing.
     */
    public function destroy(Food $food)
    {
        if ($food->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($food->image && Storage::disk('public')->exists($food->image)) {
            Storage::disk('public')->delete($food->image);
        }

        $food->delete();

        return redirect()->route('provider.dashboard')
            ->with('success', 'Food listing deleted successfully.');
    }
}
