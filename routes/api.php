<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TravelRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth.cookie')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    Route::apiResource('viagens', TravelRequestController::class)->parameters(['viagens' => 'travelRequest']);
});
