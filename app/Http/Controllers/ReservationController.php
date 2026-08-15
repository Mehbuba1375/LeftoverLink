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
            'preferred_pickup_date' => 'nullable|date|after_or_equal:today',
            'preferred_pickup_time' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        // Validate preferred pickup time falls within food listing's pickup window
        if (!empty($validated['preferred_pickup_time']) && $food->pickup_start_time && $food->pickup_end_time) {
            if (!$this->isTimeWithinWindow($validated['preferred_pickup_time'], $food->pickup_start_time, $food->pickup_end_time)) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => "Selected pickup time must fall within the food listing's pickup window ({$food->pickup_window})."], 422);
                }
                return back()->with('error', "Selected pickup time must fall within the food listing's pickup window ({$food->pickup_window}).");
            }
        }

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

            // Create reservation with schedule details
            Reservation::create([
                'user_id' => $user->id,
                'food_id' => $food->id,
                'quantity' => $validated['quantity'],
                'status' => Reservation::STATUS_RESERVED,
                'preferred_pickup_date' => $validated['preferred_pickup_date'] ?? null,
                'preferred_pickup_time' => $validated['preferred_pickup_time'] ?? null,
                'approved_pickup_date' => $validated['preferred_pickup_date'] ?? null,
                'approved_pickup_time' => $validated['preferred_pickup_time'] ?? null,
                'pickup_schedule_status' => 'pending',
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
     * Provider approves requested pickup schedule.
     */
    public function approveSchedule(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        if (!$reservation->food || ((int)$reservation->food->user_id !== (int)$user->id && !$user->isAdmin())) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            return back()->with('error', 'Unauthorized action.');
        }

        $reservation->update([
            'approved_pickup_date' => $reservation->preferred_pickup_date ?? $reservation->approved_pickup_date,
            'approved_pickup_time' => $reservation->preferred_pickup_time ?? $reservation->approved_pickup_time,
            'pickup_schedule_status' => 'approved',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pickup schedule approved successfully!']);
        }

        return back()->with('success', '✅ Pickup schedule approved successfully!');
    }

    /**
     * Provider adjusts requested pickup schedule.
     */
    public function adjustSchedule(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        if (!$reservation->food || ((int)$reservation->food->user_id !== (int)$user->id && !$user->isAdmin())) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            return back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'adjusted_pickup_date' => 'required|date|after_or_equal:today',
            'adjusted_pickup_time' => 'required|string',
        ]);

        $food = $reservation->food;

        // Validate adjusted pickup time falls within food listing's pickup window
        if ($food->pickup_start_time && $food->pickup_end_time) {
            if (!$this->isTimeWithinWindow($validated['adjusted_pickup_time'], $food->pickup_start_time, $food->pickup_end_time)) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => "Adjusted pickup time must fall within the food listing's pickup window ({$food->pickup_window})."], 422);
                }
                return back()->with('error', "Adjusted pickup time must fall within the food listing's pickup window ({$food->pickup_window}).");
            }
        }

        $reservation->update([
            'approved_pickup_date' => $validated['adjusted_pickup_date'],
            'approved_pickup_time' => $validated['adjusted_pickup_time'],
            'pickup_schedule_status' => 'adjusted',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pickup schedule adjusted successfully!']);
        }

        return back()->with('success', '✅ Pickup schedule adjusted successfully!');
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

    /**
     * Private helper to check if selected time is within start and end time window.
     */
    private function isTimeWithinWindow(string $selectedTime, string $startTime, string $endTime): bool
    {
        $selected = \Carbon\Carbon::parse($selectedTime)->format('H:i');
        $start = \Carbon\Carbon::parse($startTime)->format('H:i');
        $end = \Carbon\Carbon::parse($endTime)->format('H:i');

        return $selected >= $start && $selected <= $end;
    }
}
