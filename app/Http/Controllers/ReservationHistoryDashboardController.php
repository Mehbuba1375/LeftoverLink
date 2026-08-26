<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationHistoryDashboardController extends Controller
{
    /**
     * Display the Dedicated Consumer Reservation History Dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Query all reservations for current consumer with relationships
        $query = Reservation::where('user_id', $user->id)
            ->with(['food.user', 'review'])
            ->latest('reserved_at');

        $allReservations = $query->get();

        // Overall Stats & Financial Metrics
        $totalReservations = $allReservations->count();
        $completedCount = $allReservations->where('status', Reservation::STATUS_COMPLETED)->count();
        $cancelledCount = $allReservations->where('status', Reservation::STATUS_CANCELLED)->count();
        $activeCount = $allReservations->where('status', Reservation::STATUS_RESERVED)->count();

        // Calculate Payment & Financial Statistics
        $completedPaidReservations = $allReservations->filter(function ($res) {
            return $res->status === Reservation::STATUS_COMPLETED && $res->food && !$res->food->donation_status && $res->food->price > 0;
        });

        $totalSpent = $completedPaidReservations->sum(function ($res) {
            return $res->quantity * ($res->food->price ?? 0);
        });

        $totalRescuedItems = $allReservations->where('status', Reservation::STATUS_COMPLETED)->sum('quantity');

        $freeDonationPickups = $allReservations->filter(function ($res) {
            return $res->status === Reservation::STATUS_COMPLETED && $res->food && $res->food->donation_status;
        })->count();

        // Payment Records Collection (Formatted for Payment History Tab)
        $paymentRecords = $allReservations->map(function ($res) {
            $unitPrice = ($res->food && !$res->food->donation_status) ? (float)$res->food->price : 0.00;
            $totalAmount = $unitPrice * $res->quantity;
            $isPaid = $res->status === Reservation::STATUS_COMPLETED && $unitPrice > 0;
            $isFree = $res->food ? (bool)$res->food->donation_status : false;

            $paymentStatus = 'N/A';
            if ($isFree) {
                $paymentStatus = 'Free Donation';
            } elseif ($res->status === Reservation::STATUS_COMPLETED) {
                $paymentStatus = 'Paid on Pickup';
            } elseif ($res->status === Reservation::STATUS_CANCELLED) {
                $paymentStatus = 'Cancelled / No Charge';
            } elseif ($res->status === Reservation::STATUS_RESERVED) {
                $paymentStatus = 'Pending Pickup';
            }

            return [
                'id' => $res->id,
                'reservation_code' => 'RES-' . str_pad($res->id, 6, '0', STR_PAD_LEFT),
                'transaction_ref' => 'TXN-' . strtoupper(substr(md5($res->id . $res->created_at), 0, 10)),
                'food_title' => $res->food ? $res->food->food_name : 'Surplus Food Item',
                'provider_name' => ($res->food && $res->food->user) ? $res->food->user->name : 'Food Provider',
                'category' => $res->food ? $res->food->category : 'General',
                'quantity' => $res->quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'is_free' => $isFree,
                'payment_status' => $paymentStatus,
                'reservation_status' => $res->status,
                'reserved_at' => $res->reserved_at,
                'completed_at' => $res->completed_at,
                'cancelled_at' => $res->cancelled_at,
                'pickup_window' => $res->food ? $res->food->pickup_window : 'Flexible',
            ];
        });

        $stats = [
            'total' => $totalReservations,
            'completed' => $completedCount,
            'cancelled' => $cancelledCount,
            'active' => $activeCount,
            'total_spent' => $totalSpent,
            'total_rescued_items' => $totalRescuedItems,
            'free_pickups' => $freeDonationPickups,
        ];

        return view('reservations.history_dashboard', compact('allReservations', 'paymentRecords', 'stats'));
    }
}
