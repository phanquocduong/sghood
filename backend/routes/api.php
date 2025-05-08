<?php

use App\Http\Controllers\MotelController;
use App\Http\Controllers\MotelImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\DistrictsController;

Route::middleware('throttle:60,1')->post('/firebase-auth', [FirebaseAuthController::class, 'auth']);
Route::post('/firebase-register', [FirebaseAuthController::class, 'register']);
Route::middleware('throttle:60,1')->post('/refresh-token', [FirebaseAuthController::class, 'refreshToken']);
Route::post('/logout', [FirebaseAuthController::class, 'logout']);

Route::middleware('firebase')->group(function () {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });
});


Route::middleware('api')->group(function () {
    // districts
    Route::get('/districts', [DistrictsController::class, 'index']);
    Route::post('/districts', [DistrictsController::class, 'store']);
    Route::get('/districts/{id}', [DistrictsController::class, 'show']);
    Route::put('/districts/{id}', [DistrictsController::class, 'update']);
    Route::delete('/districts/{id}', [DistrictsController::class, 'destroy']);

    // motels
    Route::get('/motels/search', [MotelController::class, 'search']);
    Route::get('/motels', [MotelController::class, 'index']);
    Route::post('/motels', [MotelController::class, 'store']);
    Route::get('/motels/{id}', [MotelController::class, 'show']);
    Route::put('/motels/{id}', [MotelController::class, 'update']);
    Route::delete('/motels/{id}', [MotelController::class, 'destroy']);
    Route::post('/motels/restore/{id}', [MotelController::class, 'restore']);

    // motel images
    Route::get('/motel-images', [MotelImageController::class, 'index']);
    Route::post('/motel-images', [MotelImageController::class, 'store']);
    Route::get('/motel-images/{id}', [MotelImageController::class, 'show']);
    Route::put('/motel-images/{id}', [MotelImageController::class, 'update']);
    Route::delete('/motel-images/{id}', [MotelImageController::class, 'destroy']);

});

