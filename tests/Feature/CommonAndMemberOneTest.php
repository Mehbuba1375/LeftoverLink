<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\FoodRequest;
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
            'pickup_start_time' => '16:00',
            'pickup_end_time' => '19:00',
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

    public function test_available_donations_page_displays_only_donated_listings()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);

        // Donated food item
        $donatedItem = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Free Soup Meal',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 0,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '6:00 PM',
            'donation_status' => true,
        ]);

        // Discounted sale food item
        $discountedItem = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Paid Pizza Box',
            'category' => 'Prepared Meals',
            'quantity' => 5,
            'price' => 120,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '7:00 PM',
            'donation_status' => false,
        ]);

        // 1. Available Donations page GET /donations
        $response = $this->get('/donations');
        $response->assertStatus(200)
                 ->assertSee('Free Soup Meal')
                 ->assertDontSee('Paid Pizza Box');

        // 2. Search API on Available Donations page (type=donated & is_donation_page=1)
        $apiResponse = $this->get('/marketplace/api/search?is_donation_page=1&type=donated');
        $apiResponse->assertStatus(200);
        
        $data = $apiResponse->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Free Soup Meal', $data[0]['food_name']);
        $this->assertTrue($data[0]['donation_status']);

        // 3. Search "Pizza" on Available Donations API returns EMPTY because Pizza is discounted!
        $searchResponse = $this->get('/marketplace/api/search?is_donation_page=1&type=donated&search=Pizza');
        $searchResponse->assertStatus(200);
        $this->assertCount(0, $searchResponse->json('data'));
    }

    public function test_dynamic_food_rating_and_reviews_workflow()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $consumerA = User::factory()->create(['role' => 'consumer']);
        $consumerB = User::factory()->create(['role' => 'consumer']);

        // 1. Newly created listing starts with 0.0 rating and 0 reviews
        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Organic Apple Pie',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 90,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '4:00 PM',
            'donation_status' => false,
        ]);

        $this->assertEquals(0.0, $food->average_rating);
        $this->assertEquals(0, $food->reviews_count);

        // 2. First consumer submits 5-star review
        $responseA = $this->actingAs($consumerA)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 5,
            'comment' => 'Delicious and fresh!',
        ]);

        $responseA->assertRedirect();
        $this->assertEquals(5.0, $food->fresh()->average_rating);
        $this->assertEquals(1, $food->fresh()->reviews_count);

        // 3. Second consumer submits 3-star review (Average: (5 + 3) / 2 = 4.0)
        $responseB = $this->actingAs($consumerB)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 3,
            'comment' => 'Good but a bit sweet.',
        ]);

        $responseB->assertRedirect();
        $this->assertEquals(4.0, $food->fresh()->average_rating);
        $this->assertEquals(2, $food->fresh()->reviews_count);

        // 4. API Search returns dynamic rating values
        $apiRes = $this->get('/marketplace/api/search?search=Pie');
        $apiRes->assertStatus(200)
               ->assertJsonFragment([
                   'average_rating' => 4.0,
                   'reviews_count' => 2,
               ]);
    }

    public function test_consumer_can_favorite_and_unfavorite_food_listings()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Golden Croissant',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 60,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '5:00 PM',
            'donation_status' => false,
        ]);

        // 1. Toggle favorite -> Added to Favorites
        $toggleRes1 = $this->actingAs($consumer)->postJson('/favorites/toggle/' . $food->id);
        $toggleRes1->assertStatus(200)
                   ->assertJson(['is_favorited' => true, 'message' => 'Added to Favorites.']);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $consumer->id,
            'food_id' => $food->id,
        ]);

        // 2. Favorites page displays favorited item
        $favPage = $this->actingAs($consumer)->get('/favorites');
        $favPage->assertStatus(200)
                ->assertSee('Golden Croissant');

        // 3. Toggle favorite again -> Removed from Favorites
        $toggleRes2 = $this->actingAs($consumer)->postJson('/favorites/toggle/' . $food->id);
        $toggleRes2->assertStatus(200)
                   ->assertJson(['is_favorited' => false, 'message' => 'Removed from Favorites.']);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $consumer->id,
            'food_id' => $food->id,
        ]);

        // 4. Favorites page displays friendly empty state
        $favPageEmpty = $this->actingAs($consumer)->get('/favorites');
        $favPageEmpty->assertStatus(200)
                     ->assertSee('No Favorite Listings Yet');
    }

    public function test_pickup_end_time_must_be_after_pickup_start_time()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);

        // Invalid time: end time (10:00) is before start time (14:00)
        $response = $this->actingAs($provider)->post('/provider/listings', [
            'food_name' => 'Test Sandwich',
            'category' => 'Prepared Meals',
            'quantity' => 5,
            'price' => 50,
            'expiration_time' => now()->addDays(1)->format('Y-m-d\TH:i'),
            'pickup_start_time' => '14:00',
            'pickup_end_time' => '10:00',
            'donation_status' => '0',
        ]);

        $response->assertSessionHasErrors(['pickup_end_time']);
    }

    public function test_ngo_food_request_workflow()
    {
        $providerA = User::factory()->create(['role' => 'food_provider']);
        $providerB = User::factory()->create(['role' => 'food_provider']);
        $ngoUser = User::factory()->create(['role' => 'ngo']);

        // Donated food listing
        $donatedFood = Food::create([
            'user_id' => $providerA->id,
            'food_name' => 'Community Surplus Meals',
            'category' => 'Prepared Meals',
            'quantity' => 20,
            'price' => 0,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '4:00 PM - 7:00 PM',
            'donation_status' => true,
        ]);

        // Non-donated food listing (for sale)
        $saleFood = Food::create([
            'user_id' => $providerA->id,
            'food_name' => 'Paid Pizza Slice',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 100,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '4:00 PM - 7:00 PM',
            'donation_status' => false,
        ]);

        // Test 1: NGO submits collection request for donated food
        $reqRes = $this->actingAs($ngoUser)->post('/foods/' . $donatedFood->id . '/request', [
            'quantity' => 15,
            'notes' => 'Food distribution to shelter',
        ]);
        $reqRes->assertRedirect('/food-requests');

        $this->assertDatabaseHas('food_requests', [
            'user_id' => $ngoUser->id,
            'food_id' => $donatedFood->id,
            'quantity' => 15,
            'status' => 'pending',
        ]);

        // Test 2: NGO request history page displays submitted request
        $ngoPage = $this->actingAs($ngoUser)->get('/food-requests');
        $ngoPage->assertStatus(200)
                ->assertSee('Community Surplus Meals');

        // Test 3: NGO cannot request non-donated (discounted sale) food
        $reqSale = $this->actingAs($ngoUser)->postJson('/foods/' . $saleFood->id . '/request', [
            'quantity' => 5,
        ]);
        $reqSale->assertStatus(422)
                ->assertJsonFragment(['message' => 'Collection requests can only be submitted for donated food listings.']);

        // Test 4: Provider A approves the NGO request
        $foodRequest = FoodRequest::where('user_id', $ngoUser->id)->first();
        $approveRes = $this->actingAs($providerA)->post('/food-requests/' . $foodRequest->id . '/approve');
        $approveRes->assertRedirect();

        $this->assertEquals('approved', $foodRequest->fresh()->status);
        $this->assertNotNull($foodRequest->fresh()->approved_at);

        // Test 5: Authorization - Provider B cannot approve/reject Provider A's food request
        $donatedFood2 = Food::create([
            'user_id' => $providerA->id,
            'food_name' => 'Bread Loaves Donation',
            'category' => 'Bakery & Pastries',
            'quantity' => 10,
            'price' => 0,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '5:00 PM - 8:00 PM',
            'donation_status' => true,
        ]);

        $this->actingAs($ngoUser)->post('/foods/' . $donatedFood2->id . '/request', ['quantity' => 5]);
        $foodRequest2 = FoodRequest::where('food_id', $donatedFood2->id)->first();

        $unauthApprove = $this->actingAs($providerB)->postJson('/food-requests/' . $foodRequest2->id . '/approve');
        $unauthApprove->assertStatus(403);

        // Test 6: Provider A rejects the second NGO request
        $rejectRes = $this->actingAs($providerA)->post('/food-requests/' . $foodRequest2->id . '/reject');
        $rejectRes->assertRedirect();
        $this->assertEquals('rejected', $foodRequest2->fresh()->status);
    }

    public function test_ngo_reservation_management_workflow()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $ngoUser = User::factory()->create(['role' => 'ngo']);

        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Fresh Apple Baskets',
            'category' => 'Fresh Produce',
            'quantity' => 10,
            'price' => 50,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '10:00 AM – 2:00 PM',
            'pickup_start_time' => '10:00',
            'pickup_end_time' => '14:00',
            'donation_status' => false,
        ]);

        // Test 1: NGO reserves 3 items
        $reserveRes = $this->actingAs($ngoUser)->post('/foods/' . $food->id . '/reserve', [
            'quantity' => 3,
        ]);
        $reserveRes->assertRedirect('/reservations');

        // Verify stock decrements to 7
        $this->assertEquals(7, $food->fresh()->quantity);

        // Verify reservation is stored in database
        $this->assertDatabaseHas('reservations', [
            'user_id' => $ngoUser->id,
            'food_id' => $food->id,
            'quantity' => 3,
            'status' => 'reserved',
        ]);

        // Test 2: NGO Reservation History displays active reservation
        $historyRes = $this->actingAs($ngoUser)->get('/reservations');
        $historyRes->assertStatus(200)
                   ->assertSee('Fresh Apple Baskets');

        // Test 3: Provider updates NGO reservation status to Completed
        $reservation = \App\Models\Reservation::where('user_id', $ngoUser->id)->first();
        $compRes = $this->actingAs($provider)->post('/reservations/' . $reservation->id . '/complete');
        $compRes->assertRedirect();

        $this->assertEquals('completed', $reservation->fresh()->status);
        $this->assertNotNull($reservation->fresh()->completed_at);

        // Test 4: NGO Reservation History reflects updated status
        $historyRes2 = $this->actingAs($ngoUser)->get('/reservations');
        $historyRes2->assertStatus(200)
                    ->assertSee('Completed');
    }

    public function test_provider_dashboard_displays_incoming_reservations_with_isolation()
    {
        $providerA = User::factory()->create(['role' => 'food_provider']);
        $providerB = User::factory()->create(['role' => 'food_provider']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $foodA = Food::create([
            'user_id' => $providerA->id,
            'food_name' => 'Provider A Special Lasagna',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 150,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '12:00 PM - 3:00 PM',
            'pickup_start_time' => '12:00',
            'pickup_end_time' => '15:00',
            'donation_status' => false,
        ]);

        $foodB = Food::create([
            'user_id' => $providerB->id,
            'food_name' => 'Provider B Tasty Burger',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 120,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '1:00 PM - 4:00 PM',
            'pickup_start_time' => '13:00',
            'pickup_end_time' => '16:00',
            'donation_status' => false,
        ]);

        // Consumer reserves Food A (Provider A's listing)
        $this->actingAs($consumer)->post('/foods/' . $foodA->id . '/reserve', ['quantity' => 2]);

        // Consumer reserves Food B (Provider B's listing)
        $this->actingAs($consumer)->post('/foods/' . $foodB->id . '/reserve', ['quantity' => 3]);

        // Test Provider A Dashboard: sees reservation for Food A, but NOT Food B
        $dashA = $this->actingAs($providerA)->get('/provider/dashboard');
        $dashA->assertStatus(200)
              ->assertSee('Provider A Special Lasagna')
              ->assertDontSee('Provider B Tasty Burger');

        // Test Provider B Dashboard: sees reservation for Food B, but NOT Food A
        $dashB = $this->actingAs($providerB)->get('/provider/dashboard');
        $dashB->assertStatus(200)
              ->assertSee('Provider B Tasty Burger')
              ->assertDontSee('Provider A Special Lasagna');

        // Provider A marks Consumer reservation for Food A as Completed
        $resA = \App\Models\Reservation::where('food_id', $foodA->id)->first();
        $completeResA = $this->actingAs($providerA)->post('/reservations/' . $resA->id . '/complete');
        $completeResA->assertRedirect();
        $this->assertEquals('completed', $resA->fresh()->status);
        $this->assertNotNull($resA->fresh()->completed_at);

        // Verify Consumer history shows Completed
        $consumerHist = $this->actingAs($consumer)->get('/reservations');
        $consumerHist->assertStatus(200)->assertSee('Completed');

        // Provider B reserves Food A (Provider A's listing)
        $this->actingAs($providerB)->post('/foods/' . $foodA->id . '/reserve', ['quantity' => 1]);
        $resB = \App\Models\Reservation::where('user_id', $providerB->id)->first();

        // Provider A marks Provider B's reservation as Completed
        $completeResB = $this->actingAs($providerA)->post('/reservations/' . $resB->id . '/complete');
        $completeResB->assertRedirect();
        $this->assertEquals('completed', $resB->fresh()->status);
        $this->assertNotNull($resB->fresh()->completed_at);

        // Verify Provider B history shows Completed
        $providerBHist = $this->actingAs($providerB)->get('/reservations');
        $providerBHist->assertStatus(200)->assertSee('Completed');
    }
}
