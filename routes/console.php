<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send SMS pickup reminders daily at 8:00 PM for next-day pickups
Schedule::command('app:send-pickup-reminders')->dailyAt('20:00');

