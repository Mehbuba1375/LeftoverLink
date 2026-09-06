<?php

use Illuminate\Support\Facades\Route;

/**
 * Bootstrap helper to register Reservation History Dashboard routes
 * without modifying existing web.php or bootstrap files.
 */
if (file_exists(base_path('routes/reservation_history_dashboard.php'))) {
    try {
        Route::middleware('web')->group(base_path('routes/reservation_history_dashboard.php'));
    } catch (\Throwable $e) {
        // Silently skip during early CLI bootstrap before facades are bound
    }
}
