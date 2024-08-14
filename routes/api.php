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
  

Route::apiResource('hotelComment', HotelCommentController::class);
Route::apiResource('restaurantComment', RestaurantCommentController::class);
Route::apiResource('bookings', BookingController::class);
Route::apiResource('hotel',  HotelController::class );
Route::apiResource('hoteldetail', HotelDetailController::class);
Route::apiResource('roomType', RoomTypeController::class);
Route::apiResource('order', OrderController::class);
Route::apiResource('orderDetail', OrderDetailController::class);
Route::apiResource('product', ProductController::class);
Route::apiResource('restaurant', RestaurantController::class);
Route::apiResource('user', UserController::class);