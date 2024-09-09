<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Hotel\HotelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\User\UserController;

/* 
                        ================================
                        ||     Level of user types    ||
                        ================================
                        //- admin                     || 
                        ||- restaurant  \   hotel     ||       
                        ||- customer                  ||      
                        ================================
 */
// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
 
    /* protected routes */
    Route::get('profile', [UserController::class, 'profile'])->middleware('auth:sanctum');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// protected routes
Route::middleware('auth:sanctum')->prefix('hotel')->group(function () {
    Route::get('list',          [HotelController::class, 'index']);
    Route::post('create',       [HotelController::class, 'store']);
    Route::post('update',       [HotelController::class, 'update']);
    Route::post('delete',       [HotelController::class, 'destroy']);
    Route::get('show/{id}',     [HotelController::class, 'show']);
    
    Route::post('book',         [HotelController::class, 'book']);
});

// protected routes
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('list',          [UserController::class, 'index']);
    Route::post('create',       [UserController::class, 'store']);
    Route::post('update',       [UserController::class, 'update']);
    Route::post('delete',       [UserController::class, 'destroy']);
    Route::get('show/{id}',     [UserController::class, 'show']);
});
  




Route::prefix('popular')->group(function () {
    Route::get('restaurants', [RestaurantController::class, 'popular']);
    Route::get('hotels', [HotelController::class, 'popular']);
    Route::get('provinces', [ProvinceController::class, 'popular']);
});


Route::prefix('search')->group(function () {
    Route::get('hotel', [HotelController::class, 'search']);
    Route::get('restaurant', [RestaurantController::class, 'search']);
});



