<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Book\BookController;
use App\Http\Controllers\Dashboard\StatController;
use App\Http\Controllers\Hotel\HotelController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ProvinceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\Room\RoomController; 
use App\Http\Controllers\User\UserController; 
/* 
                        ================================
                        ||     Level of user types    ||
                        ================================
                        ||- admin                     || 
                        ||- restaurant  \   hotel     ||       
                        ||- customer                  ||      
                        ================================
 */


// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register',         [AuthController::class, 'register']);
    Route::post('login',            [AuthController::class, 'login']);
    Route::post('forgot-password',  [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('reset-password',   [AuthController::class, 'resetPassword'])->name('password.reset');

    /* protected routes */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// protected routes
Route::middleware('auth:sanctum')->prefix('hotel')->group(function () {
    // work done
    Route::get('list',                      [HotelController::class, 'index'])->withoutMiddleware('auth:sanctum');
    Route::post('create',                   [HotelController::class, 'store']);
    Route::post('update',                   [HotelController::class, 'update']);
    Route::post('delete',                   [HotelController::class, 'destroy']);
    Route::get('show/{id}',                 [HotelController::class, 'show'])->withoutMiddleware('auth:sanctum');
    Route::get('rooms/{id}',                [RoomController::class, 'rooms'])->withoutMiddleware('auth:sanctum');
    Route::post('add-room',                 [RoomController::class, 'addRooms']);
    Route::post('upload-room/{roomId}',     [RoomController::class, 'updateRoom']);
    Route::post('delete-room',              [RoomController::class, 'deleteRoom']);

    Route::post('book',                     [BookController::class, 'book']); 
});
// work done
Route::middleware('auth:sanctum')->prefix('province')->group(function () {
    Route::get('list',                      [ProvinceController::class, 'index'])->withoutMiddleware('auth:sanctum');
    Route::post('update/{id}',              [ProvinceController::class, 'update']);
});

// protected routes
Route::middleware('auth:sanctum')->prefix('restaurant')->group(function () {
    Route::get('list',                      [RestaurantController::class, 'index']);
    Route::post('create',                   [RestaurantController::class, 'store']);
    Route::post('update',                   [RestaurantController::class, 'update']);
    Route::post('delete',                   [RestaurantController::class, 'destroy']);
    Route::get('show/{id}',                 [RestaurantController::class, 'show']);

    Route::get('menu/{id}',                 [ProductController::class, 'menu']);
    Route::post('menu/add-item',            [ProductController::class, 'addItem']);
    Route::post('menu/toggle-stock',        [ProductController::class, 'toggleItemStock']);
    Route::post('menu/remove-stock',        [ProductController::class, 'removeItemStock']);
    Route::post('checkout',                 [RestaurantController::class, 'checkout']);
    // Route::post('checkout',              [RestaurantController::class, 'checkout']);
});

Route::middleware('auth:sanctum')->prefix('product')->group(function () {
    Route::get('list',                      [ProductController::class, 'index']);
    Route::post('create',                   [ProductController::class, 'store']);
    Route::post('update',                   [ProductController::class, 'update']);
    Route::post('delete',                   [ProductController::class, 'destroy']);
    Route::get('show/{id}',                 [ProductController::class, 'show']);
});


// protected routes
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('list',          [UserController::class, 'index']);
    Route::post('create',       [UserController::class, 'store']);
    Route::post('update/{id}',  [UserController::class, 'update']);
    Route::post('delete',       [UserController::class, 'destroy']);
    Route::get('show/{id}',     [UserController::class, 'show']);
});

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('week',              [StatController::class, 'getTotalSalesThisWeek']);
    Route::get('month',             [StatController::class, 'getTotalSalesThisMonth']);
    Route::get('pending',           [StatController::class, 'pendingOrders']);
    Route::get('history',           [StatController::class, 'ordersHistory']);
    
});
Route::prefix('popular')->group(function () {
    // work done
    Route::get('hotels', [HotelController::class, 'popular']);
    Route::get('provinces', [ProvinceController::class, 'popular']);

    // not done
    // work on this
    Route::get('restaurants', [RestaurantController::class, 'popular']);
});
 