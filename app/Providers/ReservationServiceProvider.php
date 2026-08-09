<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReservationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Loads the reservation routes file so we don't need to
     * modify the existing web.php or bootstrap/app.php files.
     */
    public function boot(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/reservation.php'));
    }
}
