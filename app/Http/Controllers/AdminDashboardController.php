<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Monitoring Dashboard.
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'consumers' => User::where('role', 'consumer')->count(),
            'providers' => User::where('role', 'food_provider')->count(),
            'ngos' => User::where('role', 'ngo')->count(),
            'total_listings' => Food::count(),
            'active_listings' => Food::available()->count(),
            'donations' => Food::where('donation_status', true)->count(),
            'discounted' => Food::where('donation_status', false)->count(),
        ];

        $recent_users = User::latest()->take(5)->get();
        $recent_listings = Food::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_listings'));
    }
}
