<?php

use App\Http\Controllers\Comment\HotelCommentController;
use App\Http\Controllers\Comment\RestaurantCommentController;
use App\Http\Controllers\Hotel\HotelController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hotel\BookingController;
use App\Http\Controllers\Hotel\HotelDetailController;
use App\Http\Controllers\Hotel\RoomTypeController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderDetailController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\User\UserController;
use App\Models\Restaurant;
 
Route::prefix('create')->group(function () { 
    Route::post('hotel', [HotelController::class, 'store']);
    Route::post('restaurant', [RestaurantController::class, 'store']);
});

// get popular restaurant by amount of order records
Route::get('popularRestaurants', [RestaurantController::class , 'popular']);
// get popular hotels by the amount of bookings records
Route::get('popularHotels', [HotelController::class , 'popular']);

Route::get('popularDestination', [ProvinceController::class, 'popular']); 