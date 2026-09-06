<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\SSLCommerzService;
use App\Services\TwilioSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected SSLCommerzService $sslCommerzService;

    public function __construct(SSLCommerzService $sslCommerzService)
    {
        $this->sslCommerzService = $sslCommerzService;
    }

    /**
     * Initiate online food purchase payment via SSLCommerz.
     */
    public function initiate(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'food_id' => 'required|exists:foods,id',
            'quantity' => 'required|integer|min:1',
            'preferred_pickup_date' => 'nullable|date|after_or_equal:today',
            'preferred_pickup_time' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $food = Food::findOrFail($validated['food_id']);

        // Prevent self-reservation/purchase
        if ($food->user_id === $user->id) {
            return back()->with('error', 'You cannot purchase your own food listing.');
        }

        // Validate stock and expiration
        if ($food->expiration_time <= now()) {
            return back()->with('error', 'This food item has expired and is no longer available.');
        }

        if ($food->quantity < $validated['quantity']) {
            return back()->with('error', 'Requested quantity exceeds available stock (' . $food->quantity . ' available).');
        }

        $unitPrice = (float) $food->price;
        $totalAmount = $unitPrice * $validated['quantity'];

        // Handle free food items directly without gateway redirect
        if ($food->donation_status || $totalAmount <= 0) {
            return back()->with('error', 'This item is free of charge. Please use standard reservation.');
        }

        $tranId = 'TXN_' . strtoupper(Str::random(10));

        $reservation = null;
        $payment = null;

        DB::transaction(function () use ($user, $food, $validated, $totalAmount, $tranId, &$reservation, &$payment) {
            // Decrement food quantity
            $food->decrement('quantity', $validated['quantity']);

            // Create reservation marked with pending payment
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'food_id' => $food->id,
                'quantity' => $validated['quantity'],
                'status' => Reservation::STATUS_RESERVED,
                'payment_status' => 'pending',
                'preferred_pickup_date' => $validated['preferred_pickup_date'] ?? null,
                'preferred_pickup_time' => $validated['preferred_pickup_time'] ?? null,
                'approved_pickup_date' => $validated['preferred_pickup_date'] ?? null,
                'approved_pickup_time' => $validated['preferred_pickup_time'] ?? null,
                'pickup_schedule_status' => 'pending',
                'reserved_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create initial pending Payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'food_id' => $food->id,
                'reservation_id' => $reservation->id,
                'tran_id' => $tranId,
                'amount' => $totalAmount,
                'currency' => 'BDT',
                'status' => Payment::STATUS_PENDING,
                'payment_method' => 'SSLCommerz',
            ]);

            $reservation->update(['payment_id' => $payment->id]);
        });

        // Request gateway URL from SSLCommerz API
        $initResult = $this->sslCommerzService->initiatePayment($payment, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone_number ?? '01700000000',
        ]);

        if ($initResult['status'] === 'SUCCESS' && !empty($initResult['gateway_url'])) {
            return redirect()->away($initResult['gateway_url']);
        }

        return redirect()->route('reservations.history_dashboard')
            ->with('error', 'Unable to connect to SSLCommerz gateway. Please try again.');
    }

    /**
     * Pay for an existing unpaid reservation directly via SSLCommerz.
     */
    public function payReservation(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        if ($reservation->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($reservation->payment_status === 'paid') {
            return back()->with('info', 'This reservation is already paid.');
        }

        $food = $reservation->food;
        $unitPrice = $food && !$food->donation_status ? (float) $food->price : 0.00;
        $totalAmount = $unitPrice * $reservation->quantity;

        if ($totalAmount <= 0) {
            return back()->with('info', 'No payment required for free donation items.');
        }

        $tranId = 'TXN_' . strtoupper(Str::random(10));

        $payment = Payment::create([
            'user_id' => $user->id,
            'food_id' => $food ? $food->id : null,
            'reservation_id' => $reservation->id,
            'tran_id' => $tranId,
            'amount' => $totalAmount,
            'currency' => 'BDT',
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'SSLCommerz',
        ]);

        $reservation->update([
            'payment_id' => $payment->id,
            'payment_status' => 'pending',
        ]);

        $initResult = $this->sslCommerzService->initiatePayment($payment, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone_number ?? '01700000000',
        ]);

        if ($initResult['status'] === 'SUCCESS' && !empty($initResult['gateway_url'])) {
            return redirect()->away($initResult['gateway_url']);
        }

        return back()->with('error', 'Unable to initialize SSLCommerz gateway.');
    }

    /**
     * SSLCommerz Success Callback.
     */
    public function success(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId = $request->input('val_id', 'VAL_' . strtoupper(Str::random(8)));
        $amount = (float) $request->input('amount', 0);
        $cardType = $request->input('card_type', 'SSLCOMMERZ-ONLINE');
        $cardNo = $request->input('card_no');
        $bankTranId = $request->input('bank_tran_id');

        $payment = Payment::where('tran_id', $tranId)->first();

        if (!$payment) {
            return redirect()->route('reservations.history_dashboard')
                ->with('error', 'Transaction record not found.');
        }

        // Validate payment with SSLCommerz server-to-server API
        $validation = $this->sslCommerzService->validatePayment(
            $valId,
            $tranId,
            $payment->amount,
            $payment->currency
        );

        if ($validation['is_valid']) {
            DB::transaction(function () use ($payment, $valId, $cardType, $cardNo, $bankTranId, $request) {
                $payment->update([
                    'val_id' => $valId,
                    'status' => Payment::STATUS_VALIDATED,
                    'card_type' => $cardType,
                    'card_no' => $cardNo,
                    'bank_tran_id' => $bankTranId,
                    'pay_time' => now(),
                    'raw_response' => $request->all(),
                ]);

                if ($payment->reservation) {
                    $payment->reservation->update([
                        'payment_status' => 'paid',
                        'payment_id' => $payment->id,
                    ]);
                }
            });

            // Send confirmation SMS if available
            try {
                if ($payment->reservation) {
                    app(TwilioSmsService::class)->sendReservationConfirmation($payment->reservation->load(['user', 'food']));
                }
            } catch (\Exception $e) {
                // SMS failure non-blocking
            }

            return redirect()->route('reservations.history_dashboard')
                ->with('success', '🎉 SSLCommerz Payment Successful! Transaction ID: ' . $tranId);
        }

        $payment->update(['status' => Payment::STATUS_FAILED]);

        return redirect()->route('reservations.history_dashboard')
            ->with('error', 'Payment validation failed.');
    }

    /**
     * SSLCommerz Failure Callback.
     */
    public function fail(Request $request)
    {
        $tranId = $request->input('tran_id');
        $payment = Payment::where('tran_id', $tranId)->first();

        if ($payment) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'raw_response' => $request->all(),
            ]);

            if ($payment->reservation) {
                $payment->reservation->update(['payment_status' => 'failed']);
            }
        }

        return redirect()->route('reservations.history_dashboard')
            ->with('error', '❌ SSLCommerz Payment Failed. Please try again.');
    }

    /**
     * SSLCommerz Cancel Callback.
     */
    public function cancel(Request $request)
    {
        $tranId = $request->input('tran_id');
        $payment = Payment::where('tran_id', $tranId)->first();

        if ($payment) {
            $payment->update([
                'status' => Payment::STATUS_CANCELLED,
                'raw_response' => $request->all(),
            ]);

            if ($payment->reservation) {
                $payment->reservation->update(['payment_status' => 'cancelled']);
            }
        }

        return redirect()->route('reservations.history_dashboard')
            ->with('info', '⚠️ SSLCommerz Payment was cancelled.');
    }

    /**
     * SSLCommerz IPN (Instant Payment Notification) Webhook Callback.
     */
    public function ipn(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId = $request->input('val_id');

        $payment = Payment::where('tran_id', $tranId)->first();

        if ($payment && $payment->status === Payment::STATUS_PENDING && $valId) {
            $validation = $this->sslCommerzService->validatePayment($valId, $tranId, $payment->amount);

            if ($validation['is_valid']) {
                $payment->update([
                    'val_id' => $valId,
                    'status' => Payment::STATUS_VALIDATED,
                    'card_type' => $request->input('card_type', 'SSLCOMMERZ-IPN'),
                    'pay_time' => now(),
                    'raw_response' => $request->all(),
                ]);

                if ($payment->reservation) {
                    $payment->reservation->update(['payment_status' => 'paid']);
                }
            }
        }

        return response()->json(['status' => 'IPN Processed']);
    }

    /**
     * Simulated SSLCommerz Sandbox Gateway interface for local testing.
     */
    public function mockGateway(Request $request, string $tranId)
    {
        $payment = Payment::where('tran_id', $tranId)->with(['food', 'user'])->firstOrFail();

        return view('payments.mock_gateway', compact('payment'));
    }
}
