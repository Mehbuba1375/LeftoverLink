<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationHistoryDashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_is_redirected_from_reservation_history_dashboard()
    {
        $response = $this->get(route('reservations.history_dashboard'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function consumer_can_access_reservation_history_dashboard_with_full_metrics()
    {
        // Create Consumer User
        $consumer = User::create([
            'name' => 'Consumer User',
            'email' => 'consumer@example.com',
            'password' => Hash::make('password'),
            'role' => 'consumer',
        ]);

        // Create Food Provider User
        $provider = User::create([
            'name' => 'Food Provider User',
            'email' => 'provider@example.com',
            'password' => Hash::make('password'),
            'role' => 'food_provider',
        ]);

        // Create Surplus Food Listings
        $paidFood = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Artisanal Bakery Bread Box',
            'description' => 'Fresh surplus sourdough bread',
            'category' => 'Bakery',
            'quantity' => 10,
            'price' => 150.00,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '5:00 PM - 7:00 PM',
            'donation_status' => false,
        ]);

        $freeFood = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Organic Fresh Vegetables Box',
            'description' => 'Surplus farm fresh vegetables',
            'category' => 'Produce',
            'quantity' => 5,
            'price' => 0.00,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '4:00 PM - 6:00 PM',
            'donation_status' => true,
        ]);

        // Create Completed Reservation (Paid)
        Reservation::create([
            'user_id' => $consumer->id,
            'food_id' => $paidFood->id,
            'quantity' => 2,
            'status' => Reservation::STATUS_COMPLETED,
            'reserved_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);

        // Create Cancelled Reservation
        Reservation::create([
            'user_id' => $consumer->id,
            'food_id' => $freeFood->id,
            'quantity' => 1,
            'status' => Reservation::STATUS_CANCELLED,
            'reserved_at' => now()->subDays(3),
            'cancelled_at' => now()->subDays(2),
        ]);

        // Create Active Reservation
        Reservation::create([
            'user_id' => $consumer->id,
            'food_id' => $paidFood->id,
            'quantity' => 1,
            'status' => Reservation::STATUS_RESERVED,
            'reserved_at' => now(),
        ]);

        // Act & Assert
        $response = $this->actingAs($consumer)->get(route('reservations.history_dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Reservation History Dashboard');
        $response->assertSee('Completed Pickups');
        $response->assertSee('Cancelled Pickups');
        $response->assertSee('Payment Records');
        $response->assertSee('Artisanal Bakery Bread Box');
        $response->assertSee('Organic Fresh Vegetables Box');
    }
}
