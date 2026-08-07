<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommonAndMemberOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration()
    {
        $response = $this->post('/register', [
            'name' => 'Jane Consumer',
            'email' => 'jane@example.com',
            'phone' => '+8801711223344',
            'address' => 'Banani, Dhaka',
            'role' => 'consumer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/marketplace');
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'consumer',
        ]);
    }

    public function test_food_provider_can_create_food_listing_with_all_fields()
    {
        Storage::fake('public');
        $provider = User::factory()->create(['role' => 'food_provider']);

        $file = UploadedFile::fake()->create('food.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($provider)->post('/provider/listings', [
            'food_name' => 'Artisan Bread',
            'description' => 'Made with organic sourdough flour.',
            'category' => 'Bakery & Pastries',
            'quantity' => 10,
            'price' => 100,
            'expiration_time' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'pickup_window' => '4:00 PM - 7:00 PM',
            'donation_status' => '0',
            'image' => $file,
        ]);

        $response->assertRedirect('/provider/dashboard');
        
        $this->assertDatabaseHas('foods', [
            'food_name' => 'Artisan Bread',
            'description' => 'Made with organic sourdough flour.',
            'user_id' => $provider->id,
            'donation_status' => 0,
        ]);

        $food = Food::where('food_name', 'Artisan Bread')->first();
        $this->assertNotNull($food->image);
        Storage::disk('public')->assertExists($food->image);
    }

    public function test_marketplace_filters_active_available_foods()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);

        // Active food item
        $activeFood = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Fresh Muffin',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 50,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '10:00 AM - 12:00 PM',
            'donation_status' => false,
        ]);

        // Expired food item (should NOT be shown to consumers)
        $expiredFood = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Expired Doughnut',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 20,
            'expiration_time' => now()->subHours(2),
            'pickup_window' => 'Yesterday',
            'donation_status' => false,
        ]);

        $response = $this->get('/marketplace/api/search?search=Fresh');
        $response->assertStatus(200)
                 ->assertJsonFragment(['food_name' => 'Fresh Muffin'])
                 ->assertJsonMissing(['food_name' => 'Expired Doughnut']);
    }

    public function test_marketplace_search_filter_and_sort_together()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);

        Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Cheap Bread',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 30,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '5:00 PM',
            'donation_status' => false,
        ]);

        Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Expensive Bread',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 150,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '5:00 PM',
            'donation_status' => false,
        ]);

        $response = $this->get('/marketplace/api/search?search=Bread&category=Bakery%20%26%20Pastries&sort=price_low');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals('Cheap Bread', $data[0]['food_name']);
        $this->assertEquals('Expensive Bread', $data[1]['food_name']);
    }

    public function test_search_by_provider_name_and_clearing_search()
    {
        $providerA = User::factory()->create(['name' => 'Green Bakery Provider', 'role' => 'food_provider']);
        $providerB = User::factory()->create(['name' => 'Tasty Bistro', 'role' => 'food_provider']);

        Food::create([
            'user_id' => $providerA->id,
            'food_name' => 'Sourdough Loaf',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 80,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '5:00 PM',
            'donation_status' => false,
        ]);

        Food::create([
            'user_id' => $providerB->id,
            'food_name' => 'Pasta Bowl',
            'category' => 'Prepared Meals',
            'quantity' => 5,
            'price' => 120,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '6:00 PM',
            'donation_status' => false,
        ]);

        // Search by provider name "Green Bakery"
        $response = $this->get('/marketplace/api/search?search=Green%20Bakery');
        $response->assertStatus(200)
                 ->assertJsonFragment(['food_name' => 'Sourdough Loaf'])
                 ->assertJsonMissing(['food_name' => 'Pasta Bowl']);

        // Clear search restores all
        $responseAll = $this->get('/marketplace/api/search?search=');
        $responseAll->assertStatus(200);
        $this->assertCount(2, $responseAll->json('data'));
    }
}
