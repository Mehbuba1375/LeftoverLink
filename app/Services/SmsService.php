<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?string $sid;
    protected ?string $token;
    protected ?string $from;

    public function __construct()
    {
        $this->sid   = config('services.twilio.sid');
        $this->token = config('services.twilio.token');
        $this->from  = config('services.twilio.from');
    }

    /**
     * Send an SMS message to the given phone number.
     * Falls back to log-only mode when Twilio credentials are not configured.
     *
     * @param  string  $to       The recipient phone number (E.164 format e.g. +8801700000000)
     * @param  string  $message  The SMS body text
     */
    public function send(string $to, string $message): void
    {
        // Sanitize phone number – strip spaces/dashes so it is E.164-compatible
        $to = preg_replace('/[\s\-]/', '', $to);

        // Validate we have a usable phone number
        if (empty($to) || !str_starts_with($to, '+')) {
            Log::info('[SmsService] Skipping SMS — no valid E.164 phone number.', [
                'to'      => $to,
                'message' => $message,
            ]);
            return;
        }

        // If Twilio credentials are not set, fall back to log mode
        if (empty($this->sid) || empty($this->token) || empty($this->from)) {
            Log::info('[SmsService][LOG MODE] SMS would be sent.', [
                'to'      => $to,
                'message' => $message,
            ]);
            return;
        }

        // Send via Twilio REST API using PHP's built-in HTTP (no SDK required)
        try {
            $url  = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
            $data = http_build_query([
                'To'   => $to,
                'From' => $this->from,
                'Body' => $message,
            ]);

            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => [
                        'Content-Type: application/x-www-form-urlencoded',
                        'Authorization: Basic ' . base64_encode("{$this->sid}:{$this->token}"),
                    ],
                    'content' => $data,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer'      => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $response = file_get_contents($url, false, $context);
            $decoded  = json_decode($response, true);

            if (isset($decoded['error_code'])) {
                Log::warning('[SmsService] Twilio returned an error.', [
                    'to'    => $to,
                    'error' => $decoded['message'] ?? 'Unknown',
                ]);
            } else {
                Log::info('[SmsService] SMS sent successfully via Twilio.', [
                    'to'  => $to,
                    'sid' => $decoded['sid'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            // Never crash the app due to an SMS failure
            Log::error('[SmsService] Exception while sending SMS.', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
