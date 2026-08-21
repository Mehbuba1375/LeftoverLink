<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodRequest;
use App\Models\Reservation;
use App\Models\User;

/**
 * SustainabilityController — Module 3 (SM OMER AZAM)
 *
 * Displays platform-wide environmental and social impact statistics
 * based on completed food transactions (reservations + NGO requests).
 */
class SustainabilityController extends Controller
{
    public function index()
    {
        // ── 1. Total meals rescued via consumer reservations (completed) ─────
        $completedReservations = Reservation::where('status', Reservation::STATUS_COMPLETED)
            ->with('food')
            ->get();

        $mealsRescuedViaReservations = $completedReservations->sum('quantity');

        // ── 2. Total meals rescued via NGO approved requests ─────────────────
        $approvedNgoRequests = FoodRequest::where('status', FoodRequest::STATUS_APPROVED)
            ->with('food')
            ->get();

        $mealsRescuedViaNgo = $approvedNgoRequests->sum('quantity');

        // ── 3. Combined total meals rescued ───────────────────────────────────
        $totalMealsRescued = $mealsRescuedViaReservations + $mealsRescuedViaNgo;

        // ── 4. Distinct food listings that were successfully collected ────────
        $listingsRescued = $completedReservations->pluck('food_id')
            ->merge($approvedNgoRequests->pluck('food_id'))
            ->unique()
            ->count();

        // ── 5. Environmental estimates ────────────────────────────────────────
        // Average food serving weight: ~0.5 kg per meal unit
        // CO2 equivalent saved: ~2.5 kg CO2 per kg of food waste avoided
        $estimatedKgSaved  = round($totalMealsRescued * 0.5, 1);
        $estimatedCo2Saved = round($estimatedKgSaved * 2.5, 1);

        // ── 6. Community stats ────────────────────────────────────────────────
        $activeProviders      = User::where('role', 'food_provider')
            ->whereHas('foods')
            ->count();

        $registeredNgos       = User::where('role', 'ngo')->count();
        $ngoRequestsApproved  = FoodRequest::where('status', FoodRequest::STATUS_APPROVED)->count();
        $totalListingsCreated = Food::count();
        $totalCompletedPickups = Reservation::where('status', Reservation::STATUS_COMPLETED)->count();

        // ── 7. Recent successful transactions (for live feed section) ─────────
        $recentRescues = $completedReservations
            ->sortByDesc('completed_at')
            ->take(5)
            ->values();

        return view('sustainability.index', compact(
            'totalMealsRescued',
            'mealsRescuedViaReservations',
            'mealsRescuedViaNgo',
            'listingsRescued',
            'estimatedKgSaved',
            'estimatedCo2Saved',
            'activeProviders',
            'registeredNgos',
            'ngoRequestsApproved',
            'totalListingsCreated',
            'totalCompletedPickups',
            'recentRescues'
        ));
    }
}
