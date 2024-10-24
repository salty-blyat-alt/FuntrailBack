<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Book\BookController;
use App\Http\Controllers\Comment\HotelCommentController;
use App\Http\Controllers\Dashboard\StatController;
use App\Http\Controllers\Hotel\BookingController;
use App\Http\Controllers\Hotel\HotelController;
use App\Http\Controllers\ProvinceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Room\RoomController;
use App\Http\Controllers\User\UserController;
/* 
                        ================================
                        ||     Level of user types    ||
                        ================================
                        ||- admin                     ||
                        ||- restaurant  \   hotel     ||
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
        Route::post('change-password',  [AuthController::class, 'changePassword']);
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

Route::get('success/{session_id}', [BookController::class, 'success'])->name('checkout.success');
Route::get('cancel', [BookController::class, 'cancel'])->name('checkout.cancel');

// work done
Route::middleware('auth:sanctum')->prefix('province')->group(function () {
    Route::get('list',                      [ProvinceController::class, 'index'])->withoutMiddleware('auth:sanctum');
    Route::post('update/{id}',              [ProvinceController::class, 'update']);
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

Route::prefix('comment')->group(function () {
    Route::get('list',                       [HotelCommentController::class, 'index']);
    Route::post('create',                    [HotelCommentController::class, 'store'])->middleware('auth:sanctum');
    Route::post('update',                    [HotelCommentController::class, 'update'])->middleware('auth:sanctum');
    Route::get('show/{hotel_id}',            [HotelCommentController::class, 'show']);
    Route::post('delete',                    [HotelCommentController::class, 'destroy'])->middleware('auth:sanctum');
});

Route::prefix('popular')->group(function () {
    // work done
    Route::get('hotels', [HotelController::class, 'popular']);
    Route::get('provinces', [ProvinceController::class, 'popular']);
});

Route::middleware('auth:sanctum')->prefix('order')->group(function () {
    Route::get('history',              [BookingController::class, 'history']);
});
