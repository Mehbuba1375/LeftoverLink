<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FoodRequestController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SustainabilityController;
use Illuminate\Support\Facades\Route;

// Public Marketplace Routes
Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/donations', [MarketplaceController::class, 'donations'])->name('donations.index');
Route::get('/marketplace/api/search', [MarketplaceController::class, 'searchApi'])->name('marketplace.api.search');

// Sustainability Dashboard — Module 3 (SM OMER AZAM) — Public, no auth required
Route::get('/sustainability', [SustainabilityController::class, 'index'])->name('sustainability.index');

// Toggle Favorite (Handles guest redirect internally if unauthenticated)
Route::post('/favorites/toggle/{food}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Favorites Page
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // NGO Food Request Routes
    Route::get('/food-requests', [FoodRequestController::class, 'index'])->name('food-requests.index');
    Route::post('/foods/{food}/request', [FoodRequestController::class, 'store'])->name('food-requests.store');
    Route::post('/food-requests/{foodRequest}/approve', [FoodRequestController::class, 'approve'])->name('food-requests.approve');
    Route::post('/food-requests/{foodRequest}/reject', [FoodRequestController::class, 'reject'])->name('food-requests.reject');

    // Reservation Management Routes
    Route::get('/reservations', [ReservationController::class, 'consumerHistory'])->name('reservations.index');
    Route::post('/foods/{food}/reserve', [ReservationController::class, 'store'])->name('reservations.store');
    Route::match(['post', 'patch'], '/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::match(['post', 'patch'], '/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');
    Route::match(['post', 'patch'], '/reservations/{reservation}/provider-cancel', [ReservationController::class, 'providerCancel'])->name('reservations.provider-cancel');

    // Review Submission
    Route::post('/foods/{food}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.updateInfo');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    // Food Provider Routes
    Route::middleware('role:food_provider,admin')->prefix('provider')->name('provider.')->group(function () {
        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reservations', [ReservationController::class, 'providerReservations'])->name('reservations');
        Route::get('/listings/create', [ProviderDashboardController::class, 'create'])->name('listings.create');
        Route::post('/listings', [ProviderDashboardController::class, 'store'])->name('listings.store');
        Route::put('/listings/{food}', [ProviderDashboardController::class, 'update'])->name('listings.update');
        Route::delete('/listings/{food}', [ProviderDashboardController::class, 'destroy'])->name('listings.destroy');
    });

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    });
});
