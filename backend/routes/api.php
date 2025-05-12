<?php

use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseAuthController;

Route::post('/firebase-auth', [FirebaseAuthController::class, 'auth']);
Route::post('/firebase-register', [FirebaseAuthController::class, 'register']);
Route::post('/logout', [FirebaseAuthController::class, 'logout']);

// District Routes
Route::prefix('districts')->group(function () {
    Route::get('/', [DistrictController::class, 'index']);
    Route::get('/{id}', [DistrictController::class, 'show'])->where('id', '[0-9]+');
});
Route::middleware(['firebase', 'role:Quản trị viên'])->group(function () {
    Route::prefix('districts')->group(function () {
        Route::post('/', [DistrictController::class, 'store']);
        Route::patch('/{id}', [DistrictController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [DistrictController::class, 'destroy'])->where('id', '[0-9]+');
        Route::get('/trash', [DistrictController::class, 'trash']);
        Route::post('/{id}/restore', [DistrictController::class, 'restore'])->where('id', '[0-9]+');
        Route::delete('/{id}/force', [DistrictController::class, 'forceDestroy'])->where('id', '[0-9]+');
    });
});

// Motel Routes
Route::prefix('motels')->group(function () {
    Route::get('/', [MotelController::class, 'index']);
    Route::get('/{id}', [MotelController::class, 'show']);
});
Route::middleware(['firebase', 'role:Quản trị viên'])->group(function () {
    Route::prefix('motels')->group(function () {
        Route::post('/', [MotelController::class, 'store']);
        Route::put('/{id}', [MotelController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [MotelController::class, 'destroy'])->where('id', '[0-9]+');
        Route::get('/trash', [MotelController::class, 'trash']);
        Route::post('/{id}/restore', [MotelController::class, 'restore'])->where('id', '[0-9]+');
        Route::delete('/{id}/force', [MotelController::class, 'forceDestroy'])->where('id', '[0-9]+');
    });
});

