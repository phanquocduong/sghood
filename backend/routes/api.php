<?php

use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomImageController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\RoomAmenityController;
use App\Http\Controllers\MotelAmenityController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->post('/firebase-auth', [FirebaseAuthController::class, 'auth']);
Route::post('/firebase-register', [FirebaseAuthController::class, 'register']);
Route::middleware('throttle:60,1')->post('/refresh-token', [FirebaseAuthController::class, 'refreshToken']);
Route::post('/logout', [FirebaseAuthController::class, 'logout']);

Route::middleware('firebase')->group(function () {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });
});

Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index']);
    Route::post('/', [RoomController::class, 'store']);
    Route::get('/{id}', [RoomController::class, 'show']);
    Route::patch('/{id}', [RoomController::class, 'update']);
    Route::delete('/{id}', [RoomController::class, 'destroy']);
    Route::delete('/{id}/force', [RoomController::class, 'forceDelete']);
    Route::post('/{id}/restore', [RoomController::class, 'restore']);

    Route::prefix('{roomId}/amenities')->group(function () {
        // Route cho quản lý tiện nghi của Room
        Route::get('/', [RoomAmenityController::class, 'index']);
        Route::post('/', [RoomAmenityController::class, 'store']);
        Route::delete('/{amenityId}', [RoomAmenityController::class, 'destroy']);
    });
    Route::prefix('{roomId}/images')->group(function () {
        // Route cho quản lý hình ảnh của Room
        Route::get('/', [RoomImageController::class, 'index']);
        Route::post('/', [RoomImageController::class, 'store']);
        Route::patch('/{id}', [RoomImageController::class, 'update']);
        Route::delete('/{id}', [RoomImageController::class, 'destroy']);
    });
});
Route::prefix('motels')->group(function () {
    // Route cho quản lý tiện nghi của motel
    Route::prefix('{motelId}/amenities')->group(function () {
        Route::get('/', [MotelAmenityController::class, 'index']);
        Route::post('/', [MotelAmenityController::class, 'store']);
        Route::delete('/{amenityId}', [MotelAmenityController::class, 'destroy']);
    });
});

Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenityController::class, 'index']);
    Route::post('/', [AmenityController::class, 'store']);
    Route::get('/{id}', [AmenityController::class, 'show']);
    Route::patch('/{id}', [AmenityController::class, 'update']);
    Route::delete('/{id}', [AmenityController::class, 'destroy']);
    Route::delete('/{id}/force', [AmenityController::class, 'forceDelete']);
    Route::post('/{id}/restore', [AmenityController::class, 'restore']);
});
