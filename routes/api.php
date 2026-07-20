<?php
use App\Http\Controllers\FoodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('foods/search', [FoodController::class, 'search']);
Route::apiResource('foods', FoodController::class);
