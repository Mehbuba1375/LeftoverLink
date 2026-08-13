<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Consumer creates a reservation for a food item.
     * Decrements food quantity and creates a reservation record.
     */
    public function store(Request $request, Food $food)
    {
        $user = auth()->user();

        // Prevent self-reservation: A user/provider cannot reserve their own food listing
        if ($food->user_id === $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You cannot reserve your own food listing.'], 403);
            }
            return back()->with('error', 'You cannot reserve your own food listing.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if the food item is still available (not expired, has stock)
        if ($food->expiration_time <= now()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This food item has expired and is no longer available.'], 422);
            }
            return back()->with('error', 'This food item has expired and is no longer available.');
        }

        if ($food->quantity <= 0) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This food item is out of stock.'], 422);
            }
            return back()->with('error', 'This food item is out of stock.');
        }

        if ($validated['quantity'] > $food->quantity) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Requested quantity exceeds available stock. Only ' . $food->quantity . ' items available.'], 422);
            }
            return back()->with('error', 'Requested quantity exceeds available stock. Only ' . $food->quantity . ' items available.');
        }

        // Check for existing active reservation by this user for this food
        $existingReservation = Reservation::where('user_id', $user->id)
            ->where('food_id', $food->id)
            ->where('status', Reservation::STATUS_RESERVED)
            ->first();

        if ($existingReservation) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You already have an active reservation for this food item.'], 422);
            }
            return back()->with('error', 'You already have an active reservation for this food item.');
        }

        // Use a transaction to ensure atomicity
        DB::transaction(function () use ($food, $user, $validated) {
            // Decrement food quantity
            $food->decrement('quantity', $validated['quantity']);

            // Create reservation
            Reservation::create([
                'user_id' => $user->id,
                'food_id' => $food->id,
                'quantity' => $validated['quantity'],
                'status' => Reservation::STATUS_RESERVED,
                'reserved_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Food item reserved successfully! Please pick it up during the pickup window.',
                'food_id' => $food->id,
            ]);
        }

        return redirect()->route('reservations.index')
            ->with('success', '🎉 Food item reserved successfully! Please pick it up during the pickup window.');
    }

    /**
     * Consumer cancels their own reservation.
     * Restores the quantity back to the food item.
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        // Only the reservation owner, food listing provider, or admin can cancel
        if ($reservation->user_id !== $user->id && $reservation->food->user_id !== $user->id && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            return back()->with('error', 'Unauthorized action.');
        }

        if (!$reservation->canBeCancelled()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This reservation cannot be cancelled. It is already ' . $reservation->status . '.'], 422);
            }
            return back()->with('error', 'This reservation cannot be cancelled. It is already ' . $reservation->status . '.');
        }

        DB::transaction(function () use ($reservation) {
            // Restore food quantity
            $reservation->food->increment('quantity', $reservation->quantity);

            // Update reservation status
            $reservation->update([
                'status' => Reservation::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Reservation cancelled successfully. The food item is now available again.',
                'reservation_id' => $reservation->id,
            ]);
        }

        return back()->with('success', 'Reservation cancelled successfully. The food item is now available again.');
    }

    /**
     * Provider marks a reservation as completed (pickup confirmed).
     */
    public function complete(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        // Only the food provider who owns the listing (or admin) can complete a reservation
        if (!$reservation->food || ((int)$reservation->food->user_id !== (int)$user->id && !$user->isAdmin())) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            return back()->with('error', 'Unauthorized action.');
        }

        if (!$reservation->canBeCompleted()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'This reservation cannot be completed. It is already ' . $reservation->status . '.'], 422);
            }
            return back()->with('error', 'This reservation cannot be completed. It is already ' . $reservation->status . '.');
        }

        $reservation->update([
            'status' => Reservation::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Reservation marked as completed. Pickup confirmed!',
                'reservation_id' => $reservation->id,
            ]);
        }

        return back()->with('success', '✅ Reservation marked as completed. Pickup confirmed!');
    }

    /**
     * Consumer views their reservation history (all statuses).
     */
    public function consumerHistory()
    {
        $user = auth()->user();

        $reservations = Reservation::where('user_id', $user->id)
            ->with(['food.user'])
            ->latest('reserved_at')
            ->get();

        $stats = [
            'total' => $reservations->count(),
            'active' => $reservations->where('status', Reservation::STATUS_RESERVED)->count(),
            'completed' => $reservations->where('status', Reservation::STATUS_COMPLETED)->count(),
            'cancelled' => $reservations->where('status', Reservation::STATUS_CANCELLED)->count(),
        ];

        return view('reservations.index', compact('reservations', 'stats'));
    }

    /**
     * Provider views incoming reservations on their food items.
     */
    public function providerReservations()
    {
        $user = auth()->user();

        // Get all food IDs belonging to this provider
        $foodIds = Food::where('user_id', $user->id)->pluck('id');

        $reservations = Reservation::whereIn('food_id', $foodIds)
            ->with(['user', 'food'])
            ->latest('reserved_at')
            ->get();

        $stats = [
            'total' => $reservations->count(),
            'active' => $reservations->where('status', Reservation::STATUS_RESERVED)->count(),
            'completed' => $reservations->where('status', Reservation::STATUS_COMPLETED)->count(),
            'cancelled' => $reservations->where('status', Reservation::STATUS_CANCELLED)->count(),
        ];

        return view('reservations.provider', compact('reservations', 'stats'));
    }

    /**
     * Provider cancels an incoming reservation on their food listing.
     */
    public function providerCancel(Request $request, Reservation $reservation)
    {
        return $this->cancel($request, $reservation);
    }
}
