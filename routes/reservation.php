<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reservation Routes
|--------------------------------------------------------------------------
|
| These routes handle the Reservation Management System feature.
| Loaded via ReservationServiceProvider (no changes to web.php needed).
|
*/

Route::middleware('auth')->group(function () {

    // Consumer: Reserve a food item
    Route::post('/reservations/{food}', [ReservationController::class, 'store'])
        ->name('reservations.store');

    // Consumer: View reservation history
    Route::get('/reservations', [ReservationController::class, 'consumerHistory'])
        ->name('reservations.index');

    // Consumer: Cancel a reservation
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');

    // Provider/Admin: Mark reservation as completed
    Route::patch('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])
        ->name('reservations.complete');

    // Provider: View incoming reservations on their food items
    Route::middleware('role:food_provider,admin')
        ->get('/provider/reservations', [ReservationController::class, 'providerReservations'])
        ->name('provider.reservations');
});
