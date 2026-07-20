<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FoodListing;
use App\Models\NgoFoodRequest;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@leftoverlink.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+8801700000001',
            'address' => 'Dhaka, Bangladesh',
        ]);

        $donor = User::create([
            'name' => 'Donor User',
            'email' => 'donor@leftoverlink.com',
            'password' => Hash::make('password123'),
            'role' => 'donor',
            'organization_name' => 'Labaid Group',
            'phone' => '+8801700000002',
            'address' => 'Dhanmondi, Dhaka',
        ]);

        $ngo = User::create([
            'name' => 'NGO User',
            'email' => 'ngo@leftoverlink.com',
            'password' => Hash::make('password123'),
            'role' => 'ngo',
            'organization_name' => 'BRAC Food NGO',
            'phone' => '+8801700000003',
            'address' => 'Mohakhali, Dhaka',
        ]);

        // 2. Create Food Listings
        $listing1 = FoodListing::create([
            'donor_id' => $donor->id,
            'title' => 'Fresh Rice & Chicken Curry',
            'description' => 'Packaged cooked meals from an event. Kept in clean conditions.',
            'quantity' => 50,
            'unit' => 'boxes',
            'expiry_date' => now()->addDays(1)->toDateString(),
            'pickup_location' => 'Labaid Hospital Dhanmondi Gate 2',
            'food_type' => 'cooked',
            'status' => 'available',
        ]);

        $listing2 = FoodListing::create([
            'donor_id' => $donor->id,
            'title' => 'Bulk Raw Potatoes',
            'description' => 'Unopened sacks of high quality potatoes.',
            'quantity' => 100,
            'unit' => 'kg',
            'expiry_date' => now()->addDays(14)->toDateString(),
            'pickup_location' => 'Labaid Warehouse, Tejgaon',
            'food_type' => 'raw',
            'status' => 'available',
        ]);

        $listing3 = FoodListing::create([
            'donor_id' => $donor->id,
            'title' => 'Assorted Packaged Biscuits',
            'description' => 'Boxes of high energy protein biscuits.',
            'quantity' => 200,
            'unit' => 'pieces',
            'expiry_date' => now()->addMonths(3)->toDateString(),
            'pickup_location' => 'Dhanmondi, Dhaka',
            'food_type' => 'packaged',
            'status' => 'fulfilled',
        ]);

        // 3. Create NGO Food Requests
        $request1 = NgoFoodRequest::create([
            'ngo_id' => $ngo->id,
            'food_listing_id' => $listing1->id,
            'quantity_requested' => 30,
            'message' => 'Need these for our mohakhali slum distribution program today.',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // Mark listing1 status as requested because a request exists
        $listing1->update(['status' => 'requested']);

        // 4. Create Reviews
        // Review for the Donor
        Review::create([
            'reviewer_id' => $ngo->id,
            'reviewable_id' => $donor->id,
            'reviewable_type' => User::class,
            'rating' => 5,
            'comment' => 'Always provides high quality fresh food. Great coordination!',
        ]);

        // Review for the Food Listing 3
        Review::create([
            'reviewer_id' => $ngo->id,
            'reviewable_id' => $listing3->id,
            'reviewable_type' => FoodListing::class,
            'rating' => 4,
            'comment' => 'Great biscuits, kids loved them.',
        ]);

        // 5. Generate API Tokens for Testing
        $adminToken = $admin->createToken('test_token')->plainTextToken;
        $donorToken = $donor->createToken('test_token')->plainTextToken;
        $ngoToken = $ngo->createToken('test_token')->plainTextToken;

        echo "\n============================================\n";
        echo "LEFT OVER LINK - SEED RUN SUCCESSFUL!\n";
        echo "============================================\n";
        echo "TEST CREDENTIALS & TOKENS:\n\n";
        echo "1. ADMIN USER\n";
        echo "   Email:    admin@leftoverlink.com\n";
        echo "   Password: password123\n";
        echo "   Token:    $adminToken\n\n";
        echo "2. DONOR USER\n";
        echo "   Email:    donor@leftoverlink.com\n";
        echo "   Password: password123\n";
        echo "   Token:    $donorToken\n\n";
        echo "3. NGO USER\n";
        echo "   Email:    ngo@leftoverlink.com\n";
        echo "   Password: password123\n";
        echo "   Token:    $ngoToken\n";
        echo "============================================\n\n";
    }
}
