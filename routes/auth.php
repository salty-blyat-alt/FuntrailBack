<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\VarDumper\Caster\PdoCaster;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});



Route::prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
})->middleware('auth:sanctum');
