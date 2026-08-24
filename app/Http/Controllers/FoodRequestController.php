<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodRequest;
use App\Services\TwilioSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FoodRequestController extends Controller
{
    /**
     * NGO submits a food collection request for a donated food listing.
     */
    public function store(Request $request, Food $food)
    {
        $user = auth()->user();

        // Only NGOs can submit food collection requests
        if (!$user->isNgo()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Only NGOs can submit collection requests.'], 403);
            }
            return back()->with('error', 'Only NGOs can submit collection requests.');
        }

        // Must be a donated food listing
        if (!$food->donation_status) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Collection requests can only be submitted for donated food listings.'], 422);
            }
            return back()->with('error', 'Collection requests can only be submitted for donated food listings.');
        }

        // Check if food is still available & not expired
        if ($food->expiration_time <= now()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This donated food item has expired.'], 422);
            }
            return back()->with('error', 'This donated food item has expired.');
        }

        if ($food->quantity <= 0) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This donated food item is out of stock.'], 422);
            }
            return back()->with('error', 'This donated food item is out of stock.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $food->quantity,
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for existing pending request by this NGO for this food
        $existingRequest = FoodRequest::where('user_id', $user->id)
            ->where('food_id', $food->id)
            ->where('status', FoodRequest::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You already have a pending collection request for this donated food listing.'], 422);
            }
            return back()->with('error', 'You already have a pending collection request for this donated food listing.');
        }

        FoodRequest::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'quantity' => $validated['quantity'],
            'status' => FoodRequest::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Food collection request submitted successfully! The provider will review your request.',
                'food_id' => $food->id,
            ]);
        }

        return redirect()->route('food-requests.index')
            ->with('success', '🎉 Food collection request submitted successfully! The provider will review your request.');
    }

    /**
     * NGO views their submitted food collection requests.
     */
    public function index()
    {
        $user = auth()->user();

        $requests = FoodRequest::where('user_id', $user->id)
            ->with(['food.user'])
            ->latest()
            ->get();

        $stats = [
            'total' => $requests->count(),
            'pending' => $requests->where('status', FoodRequest::STATUS_PENDING)->count(),
            'approved' => $requests->where('status', FoodRequest::STATUS_APPROVED)->count(),
            'rejected' => $requests->where('status', FoodRequest::STATUS_REJECTED)->count(),
        ];

        return view('food-requests.index', compact('requests', 'stats'));
    }

    /**
     * Food Provider approves an NGO collection request.
     */
    public function approve(Request $request, FoodRequest $foodRequest)
    {
        $user = auth()->user();

        // Authorization: Only the owner of the donated food listing (or admin) can approve requests
        if ($foodRequest->food->user_id !== $user->id && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            return back()->with('error', 'Unauthorized action.');
        }

        if (!$foodRequest->isPending()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This request has already been processed as ' . $foodRequest->status . '.'], 422);
            }
            return back()->with('error', 'This request has already been processed as ' . $foodRequest->status . '.');
        }

        DB::transaction(function () use ($foodRequest) {
            // Delete any existing approved request by the same NGO for the same food to avoid unique constraint conflict
            FoodRequest::where('user_id', $foodRequest->user_id)
                ->where('food_id', $foodRequest->food_id)
                ->where('status', FoodRequest::STATUS_APPROVED)
                ->where('id', '!=', $foodRequest->id)
                ->delete();

            // Decrement food quantity
            if ($foodRequest->food && $foodRequest->food->quantity > 0) {
                $foodRequest->food->decrement('quantity', min($foodRequest->quantity, $foodRequest->food->quantity));
            }

            $foodRequest->update([
                'status' => FoodRequest::STATUS_APPROVED,
                'approved_at' => now(),
            ]);
        });

        // Send SMS notification to NGO for approval
        try {
            app(TwilioSmsService::class)->sendNgoRequestApproved($foodRequest->load(['user', 'food']));
        } catch (\Exception $e) {
            // SMS failure should never block the approval flow
        }

        // Send SMS notification to NGO for approval
        try {
            app(TwilioSmsService::class)->sendNgoRequestApproved($foodRequest->load(['user', 'food']));
        } catch (\Exception $e) {
            // SMS failure should never block the approval flow
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'NGO food collection request approved successfully!',
                'request_id' => $foodRequest->id,
            ]);
        }

        return back()->with('success', '✅ NGO food collection request approved successfully!');
    }

    /**
     * Food Provider rejects an NGO collection request.
     */
    public function reject(Request $request, FoodRequest $foodRequest)
    {
        $user = auth()->user();

        // Authorization: Only the owner of the donated food listing (or admin) can reject requests
        if ($foodRequest->food->user_id !== $user->id && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            return back()->with('error', 'Unauthorized action.');
        }

        if (!$foodRequest->isPending()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This request has already been processed as ' . $foodRequest->status . '.'], 422);
            }
            return back()->with('error', 'This request has already been processed as ' . $foodRequest->status . '.');
        }

        $foodRequest->update([
            'status' => FoodRequest::STATUS_REJECTED,
            'rejected_at' => now(),
        ]);

        // Send SMS notification to NGO for rejection
        try {
            app(TwilioSmsService::class)->sendNgoRequestRejected($foodRequest->load(['user', 'food']));
        } catch (\Exception $e) {
            // SMS failure should never block the rejection flow
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'NGO food collection request rejected.',
                'request_id' => $foodRequest->id,
            ]);
        }

        return back()->with('success', 'NGO food collection request rejected.');
    }
}
