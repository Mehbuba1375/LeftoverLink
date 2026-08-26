<?php

use App\Http\Controllers\ReservationHistoryDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reservation History Dashboard Routes
|--------------------------------------------------------------------------
|
| Dedicated Consumer Reservation History Dashboard featuring complete
| pickup histories, completed/cancelled records, and financial statements.
|
*/

Route::middleware(['web', 'auth'])->group(function () {

    // Dedicated Reservation History Dashboard Route
    Route::get('/reservations/dashboard', [ReservationHistoryDashboardController::class, 'index'])
        ->name('reservations.history_dashboard');

    // Alias route for direct access
    Route::get('/consumer/reservation-history', [ReservationHistoryDashboardController::class, 'index'])
        ->name('consumer.reservations.history');

});
