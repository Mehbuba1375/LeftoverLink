<?php

namespace App\Services;

use App\Models\FoodRequest;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

class TwilioSmsService
{
    protected ?Client $client = null;
    protected string $fromNumber;
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = (bool) config('twilio.enabled', false);
        $this->fromNumber = config('twilio.phone_number', '');

        if ($this->enabled) {
            $sid = config('twilio.sid', '');
            $token = config('twilio.auth_token', '');

            if ($sid && $token) {
                try {
                    // Disable SSL verification for local development (Windows PHP issue)
                    if (app()->environment('local')) {
                        $curlClient = new CurlClient([
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => 0,
                        ]);
                        $this->client = new Client($sid, $token, null, null, $curlClient);
                    } else {
                        $this->client = new Client($sid, $token);
                    }
                } catch (\Exception $e) {
                    Log::error('TwilioSmsService: Failed to initialize Twilio client.', [
                        'error' => $e->getMessage(),
                    ]);
                    $this->enabled = false;
                }
            } else {
                Log::warning('TwilioSmsService: Twilio credentials are missing. SMS disabled.');
                $this->enabled = false;
            }
        }
    }

    /**
     * Send an SMS message to the given phone number.
     */
    /**
     * Sanitize phone number to E.164 format (remove spaces, dashes, etc.).
     */
    protected function sanitizePhone(string $phone): string
    {
        // Remove all non-digit and non-plus characters
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        // If it doesn't start with +, assume Bangladesh (+880)
        if (!str_starts_with($cleaned, '+')) {
            // Remove leading 0 if present (e.g., 01568910138 -> 1568910138)
            $cleaned = ltrim($cleaned, '0');
            $cleaned = '+880' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Send an SMS message to the given phone number.
     */
    public function sendSms(string $to, string $message): bool
    {
        if (!$this->enabled || !$this->client || empty($to)) {
            return false;
        }

        $to = $this->sanitizePhone($to);

        try {
            $this->client->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);

            Log::info('TwilioSmsService: SMS sent successfully.', ['to' => $to]);
            return true;
        } catch (\Exception $e) {
            Log::error('TwilioSmsService: Failed to send SMS.', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * SMS to consumer: Reservation confirmed.
     */
    public function sendReservationConfirmation(Reservation $reservation): void
    {
        $user = $reservation->user;
        $food = $reservation->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $message = "LeftoverLink: Your reservation for \"{$food->food_name}\" (x{$reservation->quantity}) has been confirmed! "
            . "Please pick it up during the pickup window.";

        if ($reservation->preferred_pickup_date) {
            $message .= " Preferred pickup: {$reservation->formatted_preferred_schedule}.";
        }

        $this->sendSms($user->phone, $message);
    }

    /**
     * SMS to consumer: Reservation cancelled.
     */
    public function sendReservationCancellation(Reservation $reservation): void
    {
        $user = $reservation->user;
        $food = $reservation->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $message = "LeftoverLink: Your reservation for \"{$food->food_name}\" has been cancelled. "
            . "The food item is now available for others.";

        $this->sendSms($user->phone, $message);
    }

    /**
     * SMS to consumer: Pickup completed.
     */
    public function sendPickupCompleted(Reservation $reservation): void
    {
        $user = $reservation->user;
        $food = $reservation->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $message = "LeftoverLink: Pickup confirmed for \"{$food->food_name}\"! "
            . "Thank you for rescuing food and reducing waste. 🌱";

        $this->sendSms($user->phone, $message);
    }

    /**
     * SMS to consumer: Pickup schedule approved by provider.
     */
    public function sendScheduleApproved(Reservation $reservation): void
    {
        $user = $reservation->user;
        $food = $reservation->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $schedule = $reservation->formatted_approved_schedule ?? 'your requested time';
        $message = "LeftoverLink: Your pickup schedule for \"{$food->food_name}\" has been approved! "
            . "Pickup: {$schedule}.";

        $this->sendSms($user->phone, $message);
    }

    /**
     * SMS to consumer: Pickup schedule adjusted by provider.
     */
    public function sendScheduleAdjusted(Reservation $reservation): void
    {
        $user = $reservation->user;
        $food = $reservation->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $schedule = $reservation->formatted_approved_schedule ?? 'a new time';
        $message = "LeftoverLink: The provider has adjusted your pickup schedule for \"{$food->food_name}\". "
            . "New pickup: {$schedule}. Please plan accordingly.";

        $this->sendSms($user->phone, $message);
    }

    /**
     * SMS to NGO: Food collection request approved.
     */
    public function sendNgoRequestApproved(FoodRequest $foodRequest): void
    {
        $user = $foodRequest->user;
        $food = $foodRequest->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $message = "LeftoverLink: Your food collection request for \"{$food->food_name}\" (x{$foodRequest->quantity}) "
            . "has been approved! Please coordinate pickup with the provider.";

        $this->sendSms($user->phone, $message);
    }

    /**
     * SMS to NGO: Food collection request rejected.
     */
    public function sendNgoRequestRejected(FoodRequest $foodRequest): void
    {
        $user = $foodRequest->user;
        $food = $foodRequest->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $message = "LeftoverLink: Your food collection request for \"{$food->food_name}\" "
            . "has been declined by the provider. Please check other available donations.";

        $this->sendSms($user->phone, $message);
    }

    /**
     * SMS to consumer: Pickup reminder (sent day before scheduled pickup).
     */
    public function sendPickupReminder(Reservation $reservation): void
    {
        $user = $reservation->user;
        $food = $reservation->food;

        if (!$user || !$food || empty($user->phone)) {
            return;
        }

        $schedule = $reservation->formatted_approved_schedule ?? 'tomorrow';
        $message = "LeftoverLink Reminder: You have a food pickup scheduled for {$schedule}. "
            . "Item: \"{$food->food_name}\" (x{$reservation->quantity}). Don't forget! 🕐";

        $this->sendSms($user->phone, $message);
    }
}
