<?php

use App\Http\Controllers\MotelController;
use App\Http\Controllers\MotelImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\DistrictsController;

Route::post('/firebase-auth', [FirebaseAuthController::class, 'auth']);
Route::post('/firebase-register', [FirebaseAuthController::class, 'register']);
Route::post('/logout', [FirebaseAuthController::class, 'logout']);
Route::post('/refresh-token', [FirebaseAuthController::class, 'refreshToken']);

Route::middleware('firebase')->group(function () {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });
});


// District
Route::prefix('districts')->group(function () {
    Route::get('/', [DistrictsController::class, 'index']);
    Route::post('/', [DistrictsController::class, 'store']);
    Route::get('/{id}', [DistrictsController::class, 'show']);
    Route::put('/{id}', [DistrictsController::class, 'update']);
    Route::delete('/{id}', [DistrictsController::class, 'destroy']);
});

// Motels
Route::prefix('motels')->group(function () {
    Route::get('/search', [MotelController::class, 'search']);
    Route::get('/', [MotelController::class, 'index']);
    Route::post('/', [MotelController::class, 'store']);
    Route::get('/{id}', [MotelController::class, 'show']);
    Route::put('/{id}', [MotelController::class, 'update']);
    Route::delete('/{id}', [MotelController::class, 'destroy']);
    Route::post('/restore/{id}', [MotelController::class, 'restore']);
});

// Motel images
Route::prefix('motel-images')->group(function () {
    Route::get('/', [MotelImageController::class, 'index']);
    Route::post('/', [MotelImageController::class, 'store']);
    Route::get('/{id}', [MotelImageController::class, 'show']);
    Route::put('/{id}', [MotelImageController::class, 'update']);
    Route::delete('/{id}', [MotelImageController::class, 'destroy']);
});

