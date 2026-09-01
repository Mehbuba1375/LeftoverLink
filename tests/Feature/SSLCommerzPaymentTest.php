<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SSLCommerzPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $consumer;
    protected User $provider;
    protected Food $paidFood;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consumer = User::factory()->create([
            'role' => 'consumer',
            'email' => 'consumer@example.com',
        ]);

        $this->provider = User::factory()->create([
            'role' => 'food_provider',
            'email' => 'provider@example.com',
        ]);

        $this->paidFood = Food::create([
            'user_id' => $this->provider->id,
            'food_name' => 'Discounted Gourmet Meal',
            'quantity' => 10,
            'price' => 250.00,
            'donation_status' => false,
            'category' => 'Cooked Meals',
            'expiration_time' => now()->addDays(2),
            'pickup_window' => '12:00 PM - 06:00 PM',
        ]);
    }

    public function test_consumer_can_initiate_sslcommerz_payment()
    {
        $response = $this->actingAs($this->consumer)
            ->post(route('payment.initiate'), [
                'food_id' => $this->paidFood->id,
                'quantity' => 2,
                'preferred_pickup_date' => now()->addDay()->format('Y-m-d'),
                'preferred_pickup_time' => '14:00',
                'notes' => 'Please pack securely',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'quantity' => 2,
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('payments', [
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'amount' => 500.00,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'SSLCommerz',
        ]);
    }

    public function test_sslcommerz_success_callback_validates_payment()
    {
        $reservation = Reservation::create([
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'quantity' => 1,
            'status' => Reservation::STATUS_RESERVED,
            'payment_status' => 'pending',
            'reserved_at' => now(),
        ]);

        $payment = Payment::create([
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'reservation_id' => $reservation->id,
            'tran_id' => 'TXN_TEST_12345',
            'amount' => 250.00,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'SSLCommerz',
        ]);

        $reservation->update(['payment_id' => $payment->id]);

        $response = $this->post(route('payment.success'), [
            'tran_id' => 'TXN_TEST_12345',
            'val_id' => 'VAL_99887766',
            'amount' => 250.00,
            'card_type' => 'SSLCOMMERZ-BKASH',
            'card_no' => '017****1234',
            'bank_tran_id' => 'BANK_889900',
        ]);

        $response->assertRedirect(route('reservations.history_dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'tran_id' => 'TXN_TEST_12345',
            'val_id' => 'VAL_99887766',
            'status' => Payment::STATUS_VALIDATED,
            'card_type' => 'SSLCOMMERZ-BKASH',
        ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_sslcommerz_fail_callback_updates_status()
    {
        $payment = Payment::create([
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'tran_id' => 'TXN_FAIL_001',
            'amount' => 250.00,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'SSLCommerz',
        ]);

        $response = $this->post(route('payment.fail'), [
            'tran_id' => 'TXN_FAIL_001',
        ]);

        $response->assertRedirect(route('reservations.history_dashboard'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('payments', [
            'tran_id' => 'TXN_FAIL_001',
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    public function test_sslcommerz_cancel_callback_updates_status()
    {
        $payment = Payment::create([
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'tran_id' => 'TXN_CANCEL_001',
            'amount' => 250.00,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'SSLCommerz',
        ]);

        $response = $this->post(route('payment.cancel'), [
            'tran_id' => 'TXN_CANCEL_001',
        ]);

        $response->assertRedirect(route('reservations.history_dashboard'));
        $response->assertSessionHas('info');

        $this->assertDatabaseHas('payments', [
            'tran_id' => 'TXN_CANCEL_001',
            'status' => Payment::STATUS_CANCELLED,
        ]);
    }

    public function test_reservation_history_dashboard_renders_sslcommerz_payment_records()
    {
        $reservation = Reservation::create([
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'quantity' => 2,
            'status' => Reservation::STATUS_COMPLETED,
            'payment_status' => 'paid',
            'reserved_at' => now(),
            'completed_at' => now(),
        ]);

        $payment = Payment::create([
            'user_id' => $this->consumer->id,
            'food_id' => $this->paidFood->id,
            'reservation_id' => $reservation->id,
            'tran_id' => 'TXN_DASHBOARD_1',
            'val_id' => 'VAL_SSL_777',
            'amount' => 500.00,
            'currency' => 'BDT',
            'status' => Payment::STATUS_VALIDATED,
            'card_type' => 'SSLCOMMERZ-VISA',
            'pay_time' => now(),
        ]);

        $reservation->update(['payment_id' => $payment->id]);

        $response = $this->actingAs($this->consumer)
            ->get(route('reservations.history_dashboard'));

        $response->assertStatus(200);
        $response->assertSee('TXN_DASHBOARD_1', false);
        $response->assertSee('Paid via SSLCommerz', false);
    }
}
