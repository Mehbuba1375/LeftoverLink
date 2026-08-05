<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;
use App\Models\NgoFoodRequest;
use Illuminate\Http\Request;

class NgoFoodRequestController extends Controller
{
    // List requests (for NGOs, see their own requests; for Donors, see requests for their listings; for Admin, see all)
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $requests = NgoFoodRequest::with(['ngo', 'foodListing.donor'])->latest()->get();
        } elseif ($user->role === 'ngo') {
            $requests = NgoFoodRequest::with(['foodListing.donor'])
                ->where('ngo_id', $user->id)
                ->latest()
                ->get();
        } else { // donor
            $requests = NgoFoodRequest::with(['ngo', 'foodListing'])
                ->whereHas('foodListing', function ($query) use ($user) {
                    $query->where('donor_id', $user->id);
                })
                ->latest()
                ->get();
        }

        return response()->json($requests);
    }

    // Submit a request
    public function store(Request $request)
    {

        $request->validate([
            'food_listing_id' => 'required|exists:food_listings,id',
            'quantity_requested' => 'required|integer|min:1',
            'message' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'pickup_time' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_no' => 'nullable|string',
        ]);

        $listing = FoodListing::find($request->food_listing_id);

        if ($listing->status !== 'available') {
            return response()->json(['message' => 'This food listing is no longer available.'], 400);
        }

        if ($request->quantity_requested > $listing->quantity) {
            return response()->json(['message' => 'Requested quantity exceeds available quantity.'], 400);
        }

        $foodRequest = NgoFoodRequest::create([
            'ngo_id' => $request->ngo_id ?? 3,
            'food_listing_id' => $request->food_listing_id,
            'quantity_requested' => $request->quantity_requested,
            'message' => $request->message,
            'contact_name' => $request->contact_name,
            'pickup_time' => $request->pickup_time,
            'address' => $request->address,
            'contact_no' => $request->contact_no,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // Automatically update the listing status to requested (optional or keep available if quantity remains, but let's change to requested as per requirement)
        $listing->update(['status' => 'requested']);

        return response()->json([
            'message' => 'Food request submitted successfully',
            'food_request' => $foodRequest->load(['foodListing', 'ngo']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $foodRequest = NgoFoodRequest::with(['ngo', 'foodListing.donor'])->find($id);

        if (!$foodRequest) {
            return response()->json(['message' => 'Food request not found'], 404);
        }

        $user = $request->user();

        // Check authorization
        if ($user->role !== 'admin' && 
            $user->id !== $foodRequest->ngo_id && 
            $user->id !== $foodRequest->foodListing->donor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($foodRequest);
    }

    // Update request (NGOs only, when pending)
    public function update(Request $request, $id)
    {
        $foodRequest = NgoFoodRequest::find($id);

        if (!$foodRequest) {
            return response()->json(['message' => 'Food request not found'], 404);
        }

        if ($request->user()->id !== $foodRequest->ngo_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($foodRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot update a request that is already ' . $foodRequest->status], 400);
        }

        $request->validate([
            'quantity_requested' => 'sometimes|required|integer|min:1',
            'message' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'pickup_time' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_no' => 'nullable|string',
        ]);

        $listing = FoodListing::find($foodRequest->food_listing_id);
        if ($request->has('quantity_requested') && $request->quantity_requested > $listing->quantity) {
            return response()->json(['message' => 'Requested quantity exceeds available quantity.'], 400);
        }

        $foodRequest->update($request->all());

        return response()->json([
            'message' => 'Food request updated successfully',
            'food_request' => $foodRequest->load(['foodListing', 'ngo']),
        ]);
    }

    // Cancel request (NGOs only, when pending)
    public function destroy(Request $request, $id)
    {
        $foodRequest = NgoFoodRequest::find($id);

        if (!$foodRequest) {
            return response()->json(['message' => 'Food request not found'], 404);
        }

        if ($request->user()->id !== $foodRequest->ngo_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($foodRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot cancel a request that is already ' . $foodRequest->status], 400);
        }

        // Restore food listing status to available if no other pending requests
        $listing = FoodListing::find($foodRequest->food_listing_id);
        $foodRequest->delete();

        $remainingRequests = NgoFoodRequest::where('food_listing_id', $listing->id)
            ->where('status', 'pending')
            ->count();

        if ($remainingRequests === 0) {
            $listing->update(['status' => 'available']);
        }

        return response()->json([
            'message' => 'Food request cancelled and deleted successfully',
        ]);
    }

    // Process request status (Donor/Admin only)
    public function updateStatus(Request $request, $id)
    {
        $foodRequest = NgoFoodRequest::with('foodListing')->find($id);

        if (!$foodRequest) {
            return response()->json(['message' => 'Food request not found'], 404);
        }

        $listing = $foodRequest->foodListing;

        $request->validate([
            'status' => 'required|string|in:approved,rejected,fulfilled',
            'admin_notes' => 'nullable|string',
        ]);

        $newStatus = $request->status;

        if ($newStatus === 'approved') {
            // Deduct quantity
            if ($listing->quantity < $foodRequest->quantity_requested) {
                return response()->json(['message' => 'Not enough quantity in listing to approve.'], 400);
            }
            $listing->decrement('quantity', $foodRequest->quantity_requested);
            
            if ($listing->quantity === 0) {
                $listing->update(['status' => 'fulfilled']);
            }
        } elseif ($newStatus === 'rejected') {
            // Revert listing status back to available if no other pending/approved request
            $listing->update(['status' => 'available']);
        } elseif ($newStatus === 'fulfilled') {
            $listing->update(['status' => 'fulfilled']);
        }

        $foodRequest->update([
            'status' => $newStatus,
            'admin_notes' => $request->admin_notes,
            'responded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Food request status updated to ' . $newStatus,
            'food_request' => $foodRequest->load(['foodListing', 'ngo']),
        ]);
    }
}
