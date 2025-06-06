<?php

use App\Http\Controllers\Api\ContactController as ApiContactController;
use App\Http\Controllers\apis\FirebaseAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\apis\DistrictController;
use App\Http\Controllers\apis\MotelController;
use App\Http\Controllers\apis\RoomController;
use App\Http\Controllers\apis\AmenityController;
use App\Http\Controllers\apis\UserController;
use App\Http\Controllers\apis\BookmarkController;
use App\Http\Controllers\apis\ContactController;
use App\Http\Controllers\apis\ConfigController;

// Authentication Routes
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
        Route::get('/trash/{id}', [DistrictController::class, 'showTrashed'])->where('id', '[0-9]+');
        Route::post('/trash/{id}/restore', [DistrictController::class, 'restore'])->where('id', '[0-9]+');
        Route::delete('/trash/{id}/force', [DistrictController::class, 'forceDestroy'])->where('id', '[0-9]+');
    });
});

// Motel Routes
Route::prefix('motels')->group(function () {
    Route::get('/', [MotelController::class, 'index']);
    Route::get('/{id}', [MotelController::class, 'show'])->where('id', '[0-9]+');
});
Route::middleware(['firebase', 'role:Quản trị viên'])->group(function () {
    Route::prefix('motels')->group(function () {
        Route::post('/', [MotelController::class, 'store']);
        Route::patch('/{id}', [MotelController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [MotelController::class, 'destroy'])->where('id', '[0-9]+');
        Route::get('/trash', [MotelController::class, 'trash']);
        Route::get('/trash/{id}', [MotelController::class, 'showTrashed'])->where('id', '[0-9]+');
        Route::post('/trash/{id}/restore', [MotelController::class, 'restore'])->where('id', '[0-9]+');
        Route::delete('/trash/{id}/force', [MotelController::class, 'forceDestroy'])->where('id', '[0-9]+');
    });
});

// Room Routes
Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index']);
    Route::get('/{id}', [RoomController::class, 'show'])->where('id', '[0-9]+');
});
Route::middleware(['firebase', 'role:Quản trị viên'])->group(function () {
    Route::prefix('rooms')->group(function () {
        Route::post('/', [RoomController::class, 'store']);
        Route::patch('/{id}', [RoomController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [RoomController::class, 'destroy'])->where('id', '[0-9]+');
        Route::get('/trash', [RoomController::class, 'trash']);
        Route::get('/trash/{id}', [RoomController::class, 'showTrashed'])->where('id', '[0-9]+');
        Route::post('/trash/{id}/restore', [RoomController::class, 'restore'])->where('id', '[0-9]+');
        Route::delete('/trash/{id}/force', [RoomController::class, 'forceDelete'])->where('id', '[0-9]+');
    });
});

// Amenities Routes
Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenityController::class, 'index']);
    Route::get('/{id}', [AmenityController::class, 'show'])->where('id', '[0-9]+');
});
Route::middleware(['firebase', 'role:Quản trị viên'])->group(function () {
    Route::prefix('amenities')->group(function () {
        Route::post('/', [AmenityController::class, 'store']);
        Route::patch('/{id}', [AmenityController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [AmenityController::class, 'destroy'])->where('id', '[0-9]+');
        Route::get('/trash', [AmenityController::class, 'trash']);
        Route::post('/trash/{id}/restore', [AmenityController::class, 'restore'])->where('id', '[0-9]+');
        Route::delete('/trash/{id}/force', [AmenityController::class, 'forceDelete'])->where('id', '[0-9]+');
    });
});

// User Routes
Route::middleware(['firebase'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::patch('/{id}', [UserController::class, 'update']);
    });
});
Route::middleware(['firebase', 'role:Quản trị viên'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
    });
});

// Bookmark Routes
Route::prefix('bookmarks')->group(function () {
    Route::get('/', [BookmarkController::class, 'index']);
    Route::post('/', [BookmarkController::class, 'store']);
    Route::delete('/{id}', [BookmarkController::class, 'destroy']);
});

// Contact Routes
Route::post('/contact', [ContactController::class, 'send']);
// Config Routes
Route::get('/configs', [ConfigController::class, 'index']);
