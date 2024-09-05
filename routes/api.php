<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Hotel\HotelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\Restaurant\RestaurantController;

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
 
    /* protected routes */
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::prefix('popular')->group(function () {
    // get popular restaurant by amount of order records
    Route::get('restaurants', [RestaurantController::class, 'popular']);
    // get popular hotels by the amount of bookings records
    Route::get('hotels', [HotelController::class, 'popular']);

    Route::get('provinces', [ProvinceController::class, 'popular']);
});

Route::prefix('search')->group(function () {
    Route::get('hotel', [HotelController::class, 'search']);
    Route::get('restaurant', [RestaurantController::class, 'search']);
});



Route::middleware('auth:sanctum')->prefix('create')->group(function () {
    Route::post('hotel', [HotelController::class, 'store']);
    Route::post('restaurant', [RestaurantController::class, 'store']);
});