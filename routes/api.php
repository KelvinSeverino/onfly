<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TravelRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth.cookie')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware('check.user.role')->group(function () {
        Route::apiResource('usuarios', UserController::class)->parameters(['usuarios' => 'user']);
    });
    
    Route::get('viagens', [TravelRequestController::class, 'index']);
    Route::get('viagens/{travelRequest}', [TravelRequestController::class, 'show']);
    Route::post('viagens', [TravelRequestController::class, 'store']);
    Route::put('viagens/{travelRequest}', [TravelRequestController::class, 'update']);
    Route::patch('viagens/{travelRequest}/aprovar', [TravelRequestController::class, 'approve']);
    Route::patch('viagens/{travelRequest}/cancelar', [TravelRequestController::class, 'cancel']);
});
