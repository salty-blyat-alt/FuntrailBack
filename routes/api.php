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
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\User\UserController;
use App\Models\Restaurant;

// Route::apiResource('hotelComment', HotelCommentController::class);
// Route::apiResource('restaurantComment', RestaurantCommentController::class);
// Route::apiResource('bookings', BookingController::class);
// Route::apiResource('hotel',  HotelController::class );
// Route::apiResource('hoteldetail', HotelDetailController::class);
// Route::apiResource('roomType', RoomTypeController::class);
// Route::apiResource('order', OrderController::class);
// Route::apiResource('orderDetail', OrderDetailController::class);
// Route::apiResource('product', ProductController::class);
// Route::apiResource('restaurant', RestaurantController::class);
// Route::apiResource('user', UserController::class);


Route::prefix('create')->group(function () {
    
    Route::post('hotel', [HotelController::class, 'store']);
    Route::post('restaurant', [RestaurantController::class, 'store']);
});

// get popular restaurant by amount of order records
Route::get('popularRestaurants', [RestaurantController::class , 'popular']);
// get popular hotels by the amount of bookings records
Route::get('popularHotels', [HotelController::class , 'popular']);

Route::get('popularHotels', [HotelController::class , 'popular']);