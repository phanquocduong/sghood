<?php

use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomImageController;
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
});
Route::prefix('rooms/{roomId}/images')->group(function () {
    Route::get('/', [RoomImageController::class, 'index']);
    Route::post('/', [RoomImageController::class, 'store']);
    Route::patch('/{id}', [RoomImageController::class, 'update']);
    Route::delete('/{id}', [RoomImageController::class, 'destroy']);
});

