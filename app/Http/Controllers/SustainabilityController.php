<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodRequest;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class SustainabilityController extends Controller
{
    /**
     * Display the Sustainability Dashboard with platform impact statistics.
     *
     * Shows total meals rescued, food waste reduced, estimated environmental
     * impact based on completed food transactions, and monthly trends.
     */
    public function index()
    {
        // ─── Core Metrics ───────────────────────────────────────

        // Total completed reservations (successful pickups)
        $completedReservations = Reservation::where('status', Reservation::STATUS_COMPLETED)->get();
        $completedCount = $completedReservations->count();
        $completedQuantity = $completedReservations->sum('quantity');

        // Total approved NGO food requests (successful donations)
        $approvedRequests = FoodRequest::where('status', FoodRequest::STATUS_APPROVED)->get();
        $approvedCount = $approvedRequests->count();
        $approvedQuantity = $approvedRequests->sum('quantity');

        // Total meals rescued = completed reservation quantities + approved NGO request quantities
        $totalMealsRescued = $completedQuantity + $approvedQuantity;

        // Estimated food waste reduced (kg) — average 0.5 kg per meal portion
        $avgWeightPerMeal = 0.5;
        $foodWasteReducedKg = round($totalMealsRescued * $avgWeightPerMeal, 1);

        // Estimated CO₂ emissions prevented (kg) — ~2.5 kg CO₂ per kg of food waste (EPA/FAO estimate)
        $co2PerKgFood = 2.5;
        $co2Prevented = round($foodWasteReducedKg * $co2PerKgFood, 1);

        // Estimated water saved (liters) — ~1000 liters per kg of food (Water Footprint Network)
        $waterPerKgFood = 1000;
        $waterSaved = round($foodWasteReducedKg * $waterPerKgFood);

        // Community members served (unique consumers + NGOs with completed transactions)
        $consumerIds = $completedReservations->pluck('user_id')->unique();
        $ngoIds = $approvedRequests->pluck('user_id')->unique();
        $communityMembers = $consumerIds->merge($ngoIds)->unique()->count();

        // Active food providers (providers with at least one active listing)
        $activeProviders = Food::available()
            ->distinct('user_id')
            ->count('user_id');

        // Total transactions
        $totalTransactions = $completedCount + $approvedCount;

        // ─── Monthly Trends (Last 6 Months) ─────────────────────

        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('M Y');

            $monthReservations = Reservation::where('status', Reservation::STATUS_COMPLETED)
                ->whereBetween('completed_at', [$monthStart, $monthEnd])
                ->sum('quantity');

            $monthRequests = FoodRequest::where('status', FoodRequest::STATUS_APPROVED)
                ->whereBetween('approved_at', [$monthStart, $monthEnd])
                ->sum('quantity');

            $monthlyData[] = [
                'label' => $monthLabel,
                'short_label' => $monthStart->format('M'),
                'meals' => $monthReservations + $monthRequests,
            ];
        }

        $maxMonthlyMeals = max(array_column($monthlyData, 'meals') ?: [1]);

        // ─── Pass to View ───────────────────────────────────────

        $stats = [
            'total_meals_rescued' => $totalMealsRescued,
            'food_waste_reduced_kg' => $foodWasteReducedKg,
            'co2_prevented' => $co2Prevented,
            'water_saved' => $waterSaved,
            'community_members' => $communityMembers,
            'active_providers' => $activeProviders,
            'total_transactions' => $totalTransactions,
            'completed_pickups' => $completedCount,
            'ngo_donations' => $approvedCount,
        ];

        return view('sustainability.dashboard', compact('stats', 'monthlyData', 'maxMonthlyMeals'));
    }
}
