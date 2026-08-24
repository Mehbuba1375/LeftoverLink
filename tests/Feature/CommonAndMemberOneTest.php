<?php

namespace Tests\Feature;

use App\Models\Favorite;
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

        // 2. First consumer reserves food, provider completes pickup, consumer submits 5-star review
        $this->actingAs($consumerA)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $resA = \App\Models\Reservation::where('user_id', $consumerA->id)->first();
        $this->actingAs($provider)->post('/reservations/' . $resA->id . '/complete');

        $responseA = $this->actingAs($consumerA)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 5,
            'comment' => 'Delicious and fresh!',
            'reservation_id' => $resA->id,
        ]);

        $responseA->assertRedirect();
        $this->assertEquals(5.0, $food->fresh()->average_rating);
        $this->assertEquals(1, $food->fresh()->reviews_count);

        // 3. Second consumer reserves food, provider completes pickup, submits 3-star review (Average: (5 + 3) / 2 = 4.0)
        $this->actingAs($consumerB)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $resB = \App\Models\Reservation::where('user_id', $consumerB->id)->first();
        $this->actingAs($provider)->post('/reservations/' . $resB->id . '/complete');

        $responseB = $this->actingAs($consumerB)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 3,
            'comment' => 'Good but a bit sweet.',
            'reservation_id' => $resB->id,
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

    public function test_review_and_rating_system_workflow()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $ngo = User::factory()->create(['role' => 'ngo']);

        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Delicious Gourmet Pizza',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 200,
            'expiration_time' => now()->addDays(3),
            'pickup_window' => '11:00 AM - 2:00 PM',
            'pickup_start_time' => '11:00',
            'pickup_end_time' => '14:00',
            'donation_status' => false,
        ]);

        // 1. Check New Provider Has 0.0 Rating / No Reviews
        $this->assertEquals(0.0, $provider->average_rating);
        $this->assertEquals(0, $provider->reviews_count);

        // 2. Consumer reserves food
        $this->actingAs($consumer)->post('/foods/' . $food->id . '/reserve', ['quantity' => 2]);
        $reservation = \App\Models\Reservation::where('user_id', $consumer->id)->first();
        $this->assertEquals('reserved', $reservation->status);

        // 3. Test 1: Consumer CANNOT review while reservation is still 'reserved'
        $earlyReview = $this->actingAs($consumer)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 5,
            'comment' => 'Too early!',
            'reservation_id' => $reservation->id,
        ]);
        $earlyReview->assertSessionHas('error');
        $this->assertEquals(0, \App\Models\Review::count());

        // 4. Provider marks reservation as Completed
        $this->actingAs($provider)->post('/reservations/' . $reservation->id . '/complete');
        $this->assertEquals('completed', $reservation->fresh()->status);

        // 5. Test 2: Consumer CAN review after completion
        $validReview = $this->actingAs($consumer)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 5,
            'comment' => 'Amazing pizza and great pickup experience!',
            'reservation_id' => $reservation->id,
        ]);
        $validReview->assertSessionHas('success');
        $this->assertEquals(1, \App\Models\Review::count());

        // 6. Test 3: Duplicate review prevention
        $duplicateReview = $this->actingAs($consumer)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 4,
            'comment' => 'Trying to review again...',
            'reservation_id' => $reservation->id,
        ]);
        $duplicateReview->assertSessionHas('error');
        $this->assertEquals(1, \App\Models\Review::count());

        // 7. Test 4: Cancelled reservation cannot be reviewed
        $consumer2 = User::factory()->create(['role' => 'consumer']);
        $this->actingAs($consumer2)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $resCancelled = \App\Models\Reservation::where('user_id', $consumer2->id)->first();
        $this->actingAs($consumer2)->post('/reservations/' . $resCancelled->id . '/cancel');
        $this->assertEquals('cancelled', $resCancelled->fresh()->status);

        $cancelledReview = $this->actingAs($consumer2)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 1,
            'comment' => 'Cancelled reservation review test',
            'reservation_id' => $resCancelled->id,
        ]);
        $cancelledReview->assertSessionHas('error');

        // 8. Test 5: Provider average rating calculation
        $consumer3 = User::factory()->create(['role' => 'consumer']);
        $this->actingAs($consumer3)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $res3 = \App\Models\Reservation::where('user_id', $consumer3->id)->first();
        $this->actingAs($provider)->post('/reservations/' . $res3->id . '/complete');

        $this->actingAs($consumer3)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 3,
            'comment' => 'Average feedback',
            'reservation_id' => $res3->id,
        ]);

        // Average of 5 and 3 = 4.0
        $this->assertEquals(4.0, $provider->fresh()->average_rating);
        $this->assertEquals(2, $provider->fresh()->reviews_count);

        // 9. Test 6: Provider dashboard displays customer feedback
        $dashRes = $this->actingAs($provider)->get('/provider/dashboard');
        $dashRes->assertStatus(200)
                ->assertSee('Customer Reviews')
                ->assertSee('Amazing pizza and great pickup experience!')
                ->assertSee('Average feedback');
    }

    public function test_multiple_historical_completed_reservations_for_same_user_and_food()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Repeat Order Pasta',
            'category' => 'Prepared Meals',
            'quantity' => 20,
            'price' => 100,
            'expiration_time' => now()->addDays(5),
            'pickup_window' => '12:00 PM - 2:00 PM',
            'donation_status' => false,
        ]);

        // First Reservation by Consumer
        $this->actingAs($consumer)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $res1 = \App\Models\Reservation::where('user_id', $consumer->id)->latest()->first();

        // Provider completes first reservation
        $complete1 = $this->actingAs($provider)->post('/reservations/' . $res1->id . '/complete');
        $complete1->assertRedirect();
        $this->assertEquals('completed', $res1->fresh()->status);
        $this->assertNotNull($res1->fresh()->completed_at);

        // Second Reservation by SAME Consumer for SAME Food
        $this->actingAs($consumer)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $res2 = \App\Models\Reservation::where('user_id', $consumer->id)->latest('id')->first();
        $this->assertNotEquals($res1->id, $res2->id);

        // Provider completes second reservation without UniqueConstraintViolationException
        $complete2 = $this->actingAs($provider)->post('/reservations/' . $res2->id . '/complete');
        $complete2->assertRedirect();
        $this->assertEquals('completed', $res2->fresh()->status);
        $this->assertNotNull($res2->fresh()->completed_at);

        // Both historical reservations exist as Completed
        $completedCount = \App\Models\Reservation::where('user_id', $consumer->id)
            ->where('food_id', $food->id)
            ->where('status', 'completed')
            ->count();
        $this->assertEquals(2, $completedCount);
    }

    public function test_independent_reviews_for_multiple_completed_reservations_by_same_user()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Artisanal Bread',
            'category' => 'Bakery & Pastries',
            'quantity' => 20,
            'price' => 80,
            'expiration_time' => now()->addDays(3),
            'pickup_window' => '4:00 PM - 6:00 PM',
            'donation_status' => false,
        ]);

        // Purchase #1: Consumer reserves and Provider completes
        $this->actingAs($consumer)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $res1 = \App\Models\Reservation::where('user_id', $consumer->id)->latest('id')->first();
        $this->actingAs($provider)->post('/reservations/' . $res1->id . '/complete');

        // Review #1 submitted for Purchase #1 (5 stars)
        $rev1Res = $this->actingAs($consumer)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 5,
            'comment' => 'Great food!',
            'reservation_id' => $res1->id,
        ]);
        $rev1Res->assertSessionHas('success');

        // Purchase #2: SAME Consumer reserves SAME Food AGAIN and Provider completes
        $this->actingAs($consumer)->post('/foods/' . $food->id . '/reserve', ['quantity' => 1]);
        $res2 = \App\Models\Reservation::where('user_id', $consumer->id)->latest('id')->first();
        $this->actingAs($provider)->post('/reservations/' . $res2->id . '/complete');

        // Purchase #2 has NO review attached initially
        $this->assertNull($res2->fresh()->review);
        $this->assertFalse($res2->fresh()->isReviewed());

        // Review #2 submitted for Purchase #2 (3 stars)
        $rev2Res = $this->actingAs($consumer)->post('/foods/' . $food->id . '/reviews', [
            'rating' => 3,
            'comment' => 'Good, but not as fresh this time.',
            'reservation_id' => $res2->id,
        ]);
        $rev2Res->assertSessionHas('success');

        // Both review records exist independently in database
        $this->assertEquals(5, $res1->fresh()->review->rating);
        $this->assertEquals('Great food!', $res1->fresh()->review->comment);

        $this->assertEquals(3, $res2->fresh()->review->rating);
        $this->assertEquals('Good, but not as fresh this time.', $res2->fresh()->review->comment);

        // Provider average rating reflects both reviews: (5 + 3) / 2 = 4.0
        $this->assertEquals(4.0, $provider->fresh()->average_rating);
        $this->assertEquals(2, $provider->fresh()->reviews_count);
    }

    public function test_pickup_scheduling_system_workflow()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        // Food listing with pickup window 10:00 AM - 2:00 PM (10:00 - 14:00)
        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Scheduled Gourmet Lunch',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 120,
            'expiration_time' => now()->addDays(5),
            'pickup_window' => '10:00 AM – 2:00 PM',
            'pickup_start_time' => '10:00',
            'pickup_end_time' => '14:00',
            'donation_status' => false,
        ]);

        $futureDate = now()->addDays(2)->format('Y-m-d');

        // Test 1: Invalid pickup time outside window (09:00 AM is before 10:00 AM)
        $invalidRes = $this->actingAs($consumer)->post('/foods/' . $food->id . '/reserve', [
            'quantity' => 1,
            'preferred_pickup_date' => $futureDate,
            'preferred_pickup_time' => '09:00',
        ]);
        $invalidRes->assertSessionHas('error');

        // Test 2: Valid reservation with preferred date & time (11:30 AM is inside window)
        $validRes = $this->actingAs($consumer)->post('/foods/' . $food->id . '/reserve', [
            'quantity' => 2,
            'preferred_pickup_date' => $futureDate,
            'preferred_pickup_time' => '11:30',
        ]);
        $validRes->assertRedirect('/reservations');

        $reservation = \App\Models\Reservation::where('user_id', $consumer->id)->first();
        $this->assertNotNull($reservation);
        $this->assertEquals($futureDate, $reservation->preferred_pickup_date->format('Y-m-d'));
        $this->assertEquals('11:30', \Carbon\Carbon::parse($reservation->preferred_pickup_time)->format('H:i'));
        $this->assertEquals('pending', $reservation->pickup_schedule_status);
        $this->assertEquals('reserved', $reservation->status);

        // Test 3: Provider approves requested schedule
        $approveRes = $this->actingAs($provider)->post('/reservations/' . $reservation->id . '/approve-schedule');
        $approveRes->assertRedirect();

        $reservation->refresh();
        $this->assertEquals('approved', $reservation->pickup_schedule_status);
        $this->assertEquals('reserved', $reservation->status); // Remains reserved

        // Test 4: Provider adjusts schedule to another valid date & time (13:00 / 1:00 PM)
        $adjustedDate = now()->addDays(3)->format('Y-m-d');
        $adjustRes = $this->actingAs($provider)->post('/reservations/' . $reservation->id . '/adjust-schedule', [
            'adjusted_pickup_date' => $adjustedDate,
            'adjusted_pickup_time' => '13:00',
        ]);
        $adjustRes->assertRedirect();

        $reservation->refresh();
        $this->assertEquals('adjusted', $reservation->pickup_schedule_status);
        $this->assertEquals($adjustedDate, $reservation->approved_pickup_date->format('Y-m-d'));
        $this->assertEquals('13:00', \Carbon\Carbon::parse($reservation->approved_pickup_time)->format('H:i'));
        $this->assertEquals('reserved', $reservation->status); // Remains reserved

        // Test 5: Mark completed still functions correctly
        $completeRes = $this->actingAs($provider)->post('/reservations/' . $reservation->id . '/complete');
        $completeRes->assertRedirect();
        $this->assertEquals('completed', $reservation->fresh()->status);
    }

    public function test_leaflet_map_location_data_integration()
    {
        $provider = User::factory()->create(['name' => 'Green Bakery', 'role' => 'food_provider']);

        // Create food listing with specific Leaflet map coordinates
        $food = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Organic Sourdough Bread',
            'category' => 'Bakery & Pastries',
            'quantity' => 5,
            'price' => 150,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '10:00 AM – 2:00 PM',
            'pickup_start_time' => '10:00',
            'pickup_end_time' => '14:00',
            'donation_status' => false,
            'latitude' => 23.8103,
            'longitude' => 90.4125,
        ]);

        // Query search API endpoint used by Leaflet map overlay
        $response = $this->getJson('/marketplace/api/search');
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertGreaterThanOrEqual(1, $json['count']);

        $item = collect($json['data'])->firstWhere('id', $food->id);
        $this->assertNotNull($item);
        $this->assertEquals('Organic Sourdough Bread', $item['food_name']);
        $this->assertEquals('Green Bakery', $item['provider_name']);
        $this->assertEquals(23.8103, (float)$item['latitude']);
        $this->assertEquals(90.4125, (float)$item['longitude']);
    }

    public function test_user_registration_and_profile_location_selection_and_map_markers()
    {
        // 1. User registration with Leaflet map location selection
        $regData = [
            'name' => 'Loc User',
            'email' => 'locuser@example.com',
            'phone' => '+880 1700-111222',
            'role' => 'consumer',
            'latitude' => 23.7901,
            'longitude' => 90.4022,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $regResponse = $this->post('/register', $regData);
        $regResponse->assertRedirect('/marketplace');

        $user = User::where('email', 'locuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(23.7901, (float)$user->latitude);
        $this->assertEquals(90.4022, (float)$user->longitude);

        // 2. Profile update with new location
        $updateResponse = $this->actingAs($user)->post('/profile/info', [
            'name' => 'Loc User Updated',
            'email' => 'locuser@example.com',
            'phone' => '+880 1700-111222',
            'latitude' => 23.8200,
            'longitude' => 90.4200,
        ]);
        $updateResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(23.8200, (float)$user->latitude);
        $this->assertEquals(90.4200, (float)$user->longitude);

        // 3. Register a Food Provider with valid coordinates
        $provider = User::create([
            'name' => 'Blue Provider Bakery',
            'email' => 'blueprovider@example.com',
            'phone' => '+880 1800-999888',
            'role' => 'food_provider',
            'latitude' => 23.7500,
            'longitude' => 90.3800,
            'password' => bcrypt('secret123'),
        ]);

        // 4. Query marketplace search API as authenticated user
        $apiResponse = $this->actingAs($user)->getJson('/marketplace/api/search');
        $apiResponse->assertStatus(200);

        $json = $apiResponse->json();
        
        // Assert Red current user marker location payload
        $this->assertNotNull($json['current_user']);
        $this->assertEquals($user->id, $json['current_user']['id']);
        $this->assertEquals(23.8200, (float)$json['current_user']['latitude']);
        $this->assertEquals(90.4200, (float)$json['current_user']['longitude']);

        // Assert Blue food provider markers payload
        $providersPayload = collect($json['providers']);
        $providerItem = $providersPayload->firstWhere('id', $provider->id);
        $this->assertNotNull($providerItem);
        $this->assertEquals('Blue Provider Bakery', $providerItem['name']);
        $this->assertEquals(23.7500, (float)$providerItem['latitude']);
        $this->assertEquals(90.3800, (float)$providerItem['longitude']);
    }

    public function test_consumer_can_search_and_filter_favorites_only_among_their_own_saved_items()
    {
        // 1. Create two food providers
        $provider1 = User::create([
            'name' => 'Sunset Bakery House',
            'email' => 'pizzahouse@example.com',
            'phone' => '+880 1711-000111',
            'role' => 'food_provider',
            'password' => bcrypt('password'),
        ]);

        $provider2 = User::create([
            'name' => 'Italian Pasta Corner',
            'email' => 'pastacorner@example.com',
            'phone' => '+880 1711-000222',
            'role' => 'food_provider',
            'password' => bcrypt('password'),
        ]);

        // 2. Create food items
        $pizza = Food::create([
            'user_id' => $provider1->id,
            'food_name' => 'Pepperoni Pizza',
            'category' => 'Prepared Meals',
            'quantity' => 10,
            'price' => 120.00,
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '12:00 PM – 4:00 PM',
            'donation_status' => false,
        ]);

        $pasta = Food::create([
            'user_id' => $provider2->id,
            'food_name' => 'Vegetable Pasta',
            'category' => 'Prepared Meals',
            'quantity' => 5,
            'price' => 50.00,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '10:00 AM – 2:00 PM',
            'donation_status' => true,
        ]);

        $bread = Food::create([
            'user_id' => $provider1->id,
            'food_name' => 'Artisan Bread Roll',
            'category' => 'Bakery & Pastries',
            'quantity' => 8,
            'price' => 30.00,
            'expiration_time' => now()->addDays(3),
            'pickup_window' => '08:00 AM – 11:00 AM',
            'donation_status' => false,
        ]);

        $nonFavoritedBurger = Food::create([
            'user_id' => $provider1->id,
            'food_name' => 'Cheeseburger Delight',
            'category' => 'Prepared Meals',
            'quantity' => 12,
            'price' => 90.00,
            'expiration_time' => now()->addDays(1),
            'pickup_window' => '01:00 PM – 05:00 PM',
            'donation_status' => false,
        ]);

        // 3. Authenticate User A and favorite Pizza, Pasta, and Bread (NOT Cheeseburger)
        $userA = User::create([
            'name' => 'Consumer Alice',
            'email' => 'alice@example.com',
            'phone' => '+880 1800-111000',
            'role' => 'consumer',
            'password' => bcrypt('password'),
        ]);

        Favorite::create(['user_id' => $userA->id, 'food_id' => $pizza->id]);
        Favorite::create(['user_id' => $userA->id, 'food_id' => $pasta->id]);
        Favorite::create(['user_id' => $userA->id, 'food_id' => $bread->id]);

        // Test 1: Search by food name "Pizza" among favorites
        $response = $this->actingAs($userA)->getJson('/marketplace/api/search?only_favorites=1&search=Pizza');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Pepperoni Pizza', $data[0]['food_name']);

        // Test 2: Search by provider name "Italian" among favorites
        $response = $this->actingAs($userA)->getJson('/marketplace/api/search?only_favorites=1&search=Italian');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Vegetable Pasta', $data[0]['food_name']);

        // Test 3: Filter by Category "Bakery & Pastries"
        $response = $this->actingAs($userA)->getJson('/marketplace/api/search?only_favorites=1&category=' . urlencode('Bakery & Pastries'));
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Artisan Bread Roll', $data[0]['food_name']);

        // Test 4: Filter by Listing Type "donated"
        $response = $this->actingAs($userA)->getJson('/marketplace/api/search?only_favorites=1&type=donated');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Vegetable Pasta', $data[0]['food_name']);

        // Test 5: Filter by Price Range (min_price = 40, max_price = 150)
        $response = $this->actingAs($userA)->getJson('/marketplace/api/search?only_favorites=1&min_price=40&max_price=150');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data); // Pizza (120) & Pasta (50)

        // Test 6: Non-favorited item NEVER appears in favorites search
        $response = $this->actingAs($userA)->getJson('/marketplace/api/search?only_favorites=1');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
        $itemIds = collect($data)->pluck('id')->toArray();
        $this->assertNotContains($nonFavoritedBurger->id, $itemIds);
    }

    public function test_expired_favorite_food_remains_visible_with_is_expired_flag_and_cannot_be_reserved()
    {
        $provider = User::factory()->create(['role' => 'food_provider']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        // 1. Create an expired food listing
        $expiredFood = Food::create([
            'user_id' => $provider->id,
            'food_name' => 'Expired Salad Box',
            'category' => 'Fresh Produce',
            'quantity' => 5,
            'price' => 20,
            'expiration_time' => now()->subHour(), // Expired 1 hour ago
            'pickup_window' => '12:00 PM - 2:00 PM',
            'donation_status' => false,
        ]);

        // 2. Consumer favorites the expired food item
        Favorite::create(['user_id' => $consumer->id, 'food_id' => $expiredFood->id]);

        // 3. Search API for favorites returns the expired item with is_expired = true
        $response = $this->actingAs($consumer)->getJson('/marketplace/api/search?only_favorites=1');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Expired Salad Box', $data[0]['food_name']);
        $this->assertTrue($data[0]['is_expired']);

        // 4. Attempt direct reservation request for expired food fails with 422
        $res = $this->actingAs($consumer)->postJson('/foods/' . $expiredFood->id . '/reserve', [
            'quantity' => 1,
            'preferred_pickup_date' => date('Y-m-d'),
            'preferred_pickup_time' => '13:00',
        ]);

        $res->assertStatus(422);
        $res->assertJson(['message' => 'This food item has expired and is no longer available.']);
    }
}
