<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FoodListingController;
use App\Http\Controllers\NgoFoodRequestController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // User Profile / Logout
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Food Listings
    Route::apiResource('food-listings', FoodListingController::class);

    // NGO Food Requests
    Route::get('ngo/food-requests', [NgoFoodRequestController::class, 'index']);
    Route::post('ngo/food-requests', [NgoFoodRequestController::class, 'store']);
    Route::get('ngo/food-requests/{id}', [NgoFoodRequestController::class, 'show']);
    Route::put('ngo/food-requests/{id}', [NgoFoodRequestController::class, 'update']);
    Route::delete('ngo/food-requests/{id}', [NgoFoodRequestController::class, 'destroy']);
    Route::put('admin/food-requests/{id}/status', [NgoFoodRequestController::class, 'updateStatus']);

    // Reviews & Ratings
    Route::get('reviews/my-reviews', [ReviewController::class, 'myReviews']);
    Route::get('reviews/food-listings/{id}', [ReviewController::class, 'getFoodListingReviews']);
    Route::get('reviews/users/{id}', [ReviewController::class, 'getUserReviews']);
    Route::apiResource('reviews', ReviewController::class)->except(['index']);
});

// Admin-only review list (optional, but good for testing)
Route::get('reviews', [ReviewController::class, 'index'])->middleware(['auth:sanctum']);
