<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\NgoWebRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NgoWebRequestController extends Controller
{
    /**
     * Display the NGO dashboard with submitted requests.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user->isNgo() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $requests = NgoWebRequest::where('ngo_id', $user->id)
            ->with(['food.user'])
            ->latest()
            ->get();

        $stats = [
            'total' => $requests->count(),
            'pending' => $requests->where('status', NgoWebRequest::STATUS_PENDING)->count(),
            'approved' => $requests->where('status', NgoWebRequest::STATUS_APPROVED)->count(),
            'rejected' => $requests->where('status', NgoWebRequest::STATUS_REJECTED)->count(),
        ];

        return view('ngo.requests', compact('requests', 'stats'));
    }

    /**
     * Show the request form for a specific food item.
     */
    public function create(Food $food)
    {
        $user = auth()->user();

        if (!$user->isNgo() && !$user->isAdmin()) {
            return redirect()->route('home')->with('error', 'Only NGOs can request food donations.');
        }

        if (!$food->donation_status) {
            return redirect()->route('home')->with('error', 'Only donated food items can be requested.');
        }

        if ($food->quantity <= 0 || $food->expiration_time <= now()) {
            return redirect()->route('home')->with('error', 'This food item is no longer available.');
        }

        return view('ngo.create-request', compact('food'));
    }

    /**
     * Submit a collection request for a donated food item.
     */
    public function store(Request $request, Food $food)
    {
        $user = auth()->user();

        if (!$user->isNgo() && !$user->isAdmin()) {
            return redirect()->route('home')->with('error', 'Only NGOs can request food donations.');
        }

        if (!$food->donation_status) {
            return redirect()->route('home')->with('error', 'Only donated food items can be requested.');
        }

        if ($food->quantity <= 0 || $food->expiration_time <= now()) {
            return redirect()->route('home')->with('error', 'This food item is no longer available.');
        }

        $validated = $request->validate([
            'quantity_requested' => 'required|integer|min:1|max:' . $food->quantity,
            'message' => 'nullable|string|max:1000',
            'contact_name' => 'required|string|max:255',
            'pickup_time' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_no' => 'required|string|max:20',
        ]);

        // Check if NGO already has a pending request for this food
        $existing = NgoWebRequest::where('ngo_id', $user->id)
            ->where('food_id', $food->id)
            ->where('status', NgoWebRequest::STATUS_PENDING)
            ->exists();

        if ($existing) {
            return redirect()->route('ngo.requests')
                ->with('error', 'You already have a pending request for this food item.');
        }

        NgoWebRequest::create([
            'ngo_id' => $user->id,
            'food_id' => $food->id,
            'quantity_requested' => $validated['quantity_requested'],
            'message' => $validated['message'] ?? null,
            'contact_name' => $validated['contact_name'],
            'pickup_time' => $validated['pickup_time'],
            'address' => $validated['address'],
            'contact_no' => $validated['contact_no'],
            'status' => NgoWebRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        return redirect()->route('ngo.requests')
            ->with('success', '🎉 Food request submitted successfully! The provider will review your request.');
    }

    /**
     * Cancel a pending request.
     */
    public function cancel(NgoWebRequest $ngoWebRequest)
    {
        $user = auth()->user();

        if ($ngoWebRequest->ngo_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if (!$ngoWebRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $ngoWebRequest->delete();

        return back()->with('success', 'Food request cancelled successfully.');
    }

    /**
     * Display incoming NGO requests for a food provider.
     */
    public function providerRequests()
    {
        $user = auth()->user();

        if (!$user->isProvider() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Get food IDs owned by this provider
        $foodIds = Food::where('user_id', $user->id)->pluck('id');

        $requests = NgoWebRequest::whereIn('food_id', $foodIds)
            ->with(['ngo', 'food'])
            ->latest()
            ->get();

        $stats = [
            'total' => $requests->count(),
            'pending' => $requests->where('status', NgoWebRequest::STATUS_PENDING)->count(),
            'approved' => $requests->where('status', NgoWebRequest::STATUS_APPROVED)->count(),
            'rejected' => $requests->where('status', NgoWebRequest::STATUS_REJECTED)->count(),
        ];

        return view('provider.ngo-requests', compact('requests', 'stats'));
    }

    /**
     * Approve an NGO request (Provider only).
     */
    public function approve(NgoWebRequest $ngoWebRequest)
    {
        $user = auth()->user();

        if ($ngoWebRequest->food->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if (!$ngoWebRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $food = $ngoWebRequest->food;

        if ($food->quantity < $ngoWebRequest->quantity_requested) {
            return back()->with('error', 'Cannot approve: Requested quantity exceeds available stock.');
        }

        DB::transaction(function () use ($ngoWebRequest, $food) {
            // Deduct stock
            $food->decrement('quantity', $ngoWebRequest->quantity_requested);

            // Update status
            $ngoWebRequest->update([
                'status' => NgoWebRequest::STATUS_APPROVED,
                'responded_at' => now(),
            ]);
        });

        return back()->with('success', '✅ NGO Request approved successfully!');
    }

    /**
     * Reject an NGO request (Provider only).
     */
    public function reject(Request $request, NgoWebRequest $ngoWebRequest)
    {
        $user = auth()->user();

        if ($ngoWebRequest->food->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if (!$ngoWebRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $ngoWebRequest->update([
            'status' => NgoWebRequest::STATUS_REJECTED,
            'responded_at' => now(),
            'admin_notes' => $request->input('reason') ?? null,
        ]);

        return back()->with('success', '❌ NGO Request rejected successfully.');
    }
}
