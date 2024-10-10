<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Book\BookController;
use App\Http\Controllers\Hotel\HotelController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ProvinceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\Room\RoomController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;

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
    Route::get('list',                      [HotelController::class, 'index']);
    Route::post('create',                   [HotelController::class, 'store']);
    Route::post('update',                   [HotelController::class, 'update']);
    Route::post('delete',                   [HotelController::class, 'destroy']);
    Route::get('show/{id}',                 [HotelController::class, 'show']);
    Route::get('rooms/{id}',                [HotelController::class, 'rooms']);
    Route::post('add-room',                 [RoomController::class, 'addRooms']);
    Route::post('delete-room',              [RoomController::class, 'deleteRooms']);

    Route::post('book',                     [BookController::class, 'book']);
});
Route::middleware('auth:sanctum')->prefix('province')->group(function () {
    // work done
    Route::get('list',                      [ProvinceController::class, 'index']);
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


Route::prefix('popular')->group(function () {
    // work done
    Route::get('hotels', [HotelController::class, 'popular']);

    // not done
    // work on this
    Route::get('restaurants', [RestaurantController::class, 'popular']);
});

Route::get('/mock-checkout', function (Request $request) {
    // Mock data for testing purposes
    $stripePriceId = 'price_deluxe_album';
    $quantity = 1;

    // Simulated user data
    $user = $request->user();
    $userId = $user ? $user->id : null; // Mock user ID (null if not authenticated)
    $userEmail = $user ? $user->email : 'guest@example.com'; // Mock user email

    // Mockup response simulating a successful checkout session creation
    return response()->json([
        'status' => 'success',
        'message' => 'Mock checkout session created successfully.',
        'mockData' => [
            'userId' => $userId,
            'userEmail' => $userEmail,
            'stripePriceId' => $stripePriceId,
            'quantity' => $quantity, 
            'mockSessionId' => 'cs_test_mock_session_1234567890', // Mock session ID
        ]
    ]);
})->name('mock-checkout');