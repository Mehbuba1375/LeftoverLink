<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReservationHistoryDashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (file_exists(base_path('routes/reservation_history_dashboard.php'))) {
            Route::middleware('web')
                ->group(base_path('routes/reservation_history_dashboard.php'));
        }
    }
}
