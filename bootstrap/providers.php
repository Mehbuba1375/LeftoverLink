<?php

use App\Providers\AppServiceProvider;
use App\Providers\ReservationServiceProvider;
use App\Providers\ReservationHistoryDashboardServiceProvider;

return [
    AppServiceProvider::class,
    ReservationServiceProvider::class,
    ReservationHistoryDashboardServiceProvider::class,
];
