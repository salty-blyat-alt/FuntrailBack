<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HotelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::apiResource('users', UserController::class);

Route::post('register', [RegisteredUserController::class, 'store']);

Route::get('hotels', [HotelController::class, 'index'])
