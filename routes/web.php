<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// Public Marketplace Routes
Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/donations', [MarketplaceController::class, 'donations'])->name('donations.index');
Route::get('/marketplace/api/search', [MarketplaceController::class, 'searchApi'])->name('marketplace.api.search');

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

    // Review Submission
    Route::post('/foods/{food}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.updateInfo');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    // Food Provider Routes
    Route::middleware('role:food_provider,admin')->prefix('provider')->name('provider.')->group(function () {
        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
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
