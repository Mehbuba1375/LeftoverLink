<?php

namespace Database\Seeders;

use App\Models\Food;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo users & listings.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::create([
            'name' => 'Platform Administrator',
            'email' => 'admin@leftoverlink.com',
            'phone' => '+880 1700-000000',
            'address' => 'Dhaka HQ',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // 2. Food Providers
        $bakery = User::create([
            'name' => 'Green Oven Artisan Bakery',
            'email' => 'provider@bakery.com',
            'phone' => '+880 1811-223344',
            'address' => 'Road 11, Banani, Dhaka',
            'role' => 'food_provider',
            'password' => Hash::make('password'),
        ]);

        $restaurant = User::create([
            'name' => 'Tasty Harvest Bistro',
            'email' => 'provider@harvest.com',
            'phone' => '+880 1922-334455',
            'address' => 'Gulshan 2 Circle, Dhaka',
            'role' => 'food_provider',
            'password' => Hash::make('password'),
        ]);

        // 3. Consumer User
        $consumer = User::create([
            'name' => 'Sultana Rahman',
            'email' => 'consumer@example.com',
            'phone' => '+880 1633-445566',
            'address' => 'Dhanmondi 27, Dhaka',
            'role' => 'consumer',
            'password' => Hash::make('password'),
        ]);

        // 4. NGO User
        $ngo = User::create([
            'name' => 'Hope Food Relief NGO',
            'email' => 'ngo@care.org',
            'phone' => '+880 1544-556677',
            'address' => 'Mirpur 10, Dhaka',
            'role' => 'ngo',
            'password' => Hash::make('password'),
        ]);

        // 5. Seed Surplus Food Listings
        Food::create([
            'user_id' => $bakery->id,
            'food_name' => 'Fresh Sourdough Bread Loaves (Batch of 4)',
            'category' => 'Bakery & Pastries',
            'quantity' => 8,
            'price' => 120.00,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '4:00 PM - 7:00 PM Today',
            'donation_status' => false,
            'latitude' => 23.7937,
            'longitude' => 90.4066,
        ]);

        Food::create([
            'user_id' => $bakery->id,
            'food_name' => 'Croissants & Danish Pastry Box',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 180.00,
            'expiration_time' => now()->addHours(18),
            'pickup_window' => '5:30 PM - 8:00 PM Today',
            'donation_status' => false,
            'latitude' => 23.7937,
            'longitude' => 90.4066,
        ]);

        Food::create([
            'user_id' => $restaurant->id,
            'food_name' => 'Surplus Grilled Chicken Lunch Boxes',
            'category' => 'Prepared Meals',
            'quantity' => 12,
            'price' => 150.00,
            'expiration_time' => now()->addHours(12),
            'pickup_window' => '2:00 PM - 5:00 PM Today',
            'donation_status' => false,
            'latitude' => 23.7925,
            'longitude' => 90.4167,
        ]);

        Food::create([
            'user_id' => $restaurant->id,
            'food_name' => 'Organic Garden Salad Pack',
            'category' => 'Fresh Produce',
            'quantity' => 6,
            'price' => 80.00,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '3:00 PM - 6:00 PM Today',
            'donation_status' => false,
            'latitude' => 23.7925,
            'longitude' => 90.4167,
        ]);

        Food::create([
            'user_id' => $restaurant->id,
            'food_name' => 'Community Relief Rice & Curry Meals (Free Donation)',
            'category' => 'Prepared Meals',
            'quantity' => 25,
            'price' => 0.00,
            'expiration_time' => now()->addHours(24),
            'pickup_window' => '6:00 PM - 9:00 PM Today',
            'donation_status' => true,
            'latitude' => 23.7925,
            'longitude' => 90.4167,
        ]);
    }
}
