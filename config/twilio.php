<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Twilio API Credentials
    |--------------------------------------------------------------------------
    |
    | Configuration for the Twilio SMS integration used to send
    | reservation confirmations, pickup reminders, and status updates.
    |
    */

    'sid' => env('TWILIO_SID', ''),
    'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
    'phone_number' => env('TWILIO_PHONE_NUMBER', ''),
    'enabled' => env('TWILIO_ENABLED', false),

];
