<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\FoodRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Services\TwilioSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SustainabilityAndTwilioTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing sustainability dashboard.
     */
    public function test_guest_is_redirected_from_sustainability_dashboard()
    {
        $response = $this->get('/sustainability');
        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated user can access sustainability dashboard and statistics are correct.
     */
    public function test_user_can_view_sustainability_dashboard()
    {
        $user = User::factory()->create(['role' => 'consumer']);
        $provider = User::factory()->create(['role' => 'food_provider']);
        $ngo = User::factory()->create(['role' => 'ngo']);

        // Create completed food reservation transaction
        $food1 = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Pizza Slice',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 120.0,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '4:00 PM - 7:00 PM',
            'donation_status' => false,
        ]);

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'food_id' => $food1->id,
            'quantity' => 4,
            'status' => Reservation::STATUS_COMPLETED,
            'completed_at' => now(),
            'reserved_at' => now()->subHour(),
        ]);

        // Create approved NGO donation food request transaction
        $food2 = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Vegetable Curry Box',
            'category' => 'Prepared Meals',
            'quantity' => 15,
            'price' => 0.0,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '6:00 PM - 8:00 PM',
            'donation_status' => true,
        ]);

        $ngoRequest = FoodRequest::create([
            'user_id' => $ngo->id,
            'food_id' => $food2->id,
            'quantity' => 6,
            'status' => FoodRequest::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        // Access dashboard
        $response = $this->actingAs($user)->get('/sustainability');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');

        // Verify calculations:
        // total_meals_rescued = 4 (completed reservation) + 6 (approved request) = 10
        $this->assertEquals(10, $stats['total_meals_rescued']);

        // food_waste_reduced_kg = 10 * 0.5 = 5.0 kg
        $this->assertEquals(5.0, $stats['food_waste_reduced_kg']);

        // co2_prevented = 5.0 * 2.5 = 12.5 kg
        $this->assertEquals(12.5, $stats['co2_prevented']);

        // water_saved = 5.0 * 1000 = 5000 liters
        $this->assertEquals(5000, $stats['water_saved']);

        // community_members = 2 (consumer $user, and ngo $ngo)
        $this->assertEquals(2, $stats['community_members']);
    }

    /**
     * Test Twilio SMS service configuration toggles.
     */
    public function test_twilio_sms_service_deactivates_when_disabled()
    {
        config(['twilio.enabled' => false]);
        
        $smsService = new TwilioSmsService();
        $sent = $smsService->sendSms('+8801700000000', 'Test message');
        
        $this->assertFalse($sent);
    }

    /**
     * Test pickup reminder Artisan command runs successfully.
     */
    public function test_pickup_reminders_command_executes()
    {
        // Assert no error is thrown when running the reminders command
        $exitCode = Artisan::call('app:send-pickup-reminders');
        $this->assertEquals(0, $exitCode);
    }
}
