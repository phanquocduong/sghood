<?php

use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseAuthController;

Route::post('/firebase-auth', [FirebaseAuthController::class, 'auth']);
Route::post('/firebase-register', [FirebaseAuthController::class, 'register']);
Route::post('/logout', [FirebaseAuthController::class, 'logout']);
Route::post('/refresh-token', [FirebaseAuthController::class, 'refreshToken']);

Route::middleware('firebase')->group(function () {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });
});


// District Routes
Route::prefix('districts')->group(function () {
    Route::get('/', [DistrictController::class, 'index']);
    Route::get('/{id}', [DistrictController::class, 'show']);
    Route::post('/', [DistrictController::class, 'store']);
    Route::patch('/{id}', [DistrictController::class, 'update']);
    Route::delete('/{id}', [DistrictController::class, 'destroy']);
    Route::get('/trash', [DistrictController::class, 'trash']);
    Route::post('/{id}/restore', [DistrictController::class, 'restore']);
    Route::delete('/{id}/force', [DistrictController::class, 'forceDestroy']);
});

// Motel Routes
Route::prefix('motels')->group(function () {
    Route::get('/', [MotelController::class, 'index']);
    Route::get('/{id}', [MotelController::class, 'show']);
    Route::post('/', [MotelController::class, 'store']);
    Route::put('/{id}', [MotelController::class, 'update']);
    Route::delete('/{id}', [MotelController::class, 'destroy']);
    Route::get('/trash', [MotelController::class, 'trash']);
    Route::post('/{id}/restore', [MotelController::class, 'restore']);
    Route::delete('/{id}/force', [MotelController::class, 'forceDestroy']);
});
