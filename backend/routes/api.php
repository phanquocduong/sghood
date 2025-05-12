<?php
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index']);
    Route::post('/', [RoomController::class, 'store']);
    Route::get('/{id}', [RoomController::class, 'show']);
    Route::patch('/{id}', [RoomController::class, 'update']);
    Route::delete('/{id}', [RoomController::class, 'destroy']);
    Route::delete('/{id}/force', [RoomController::class, 'forceDelete']);
    Route::post('/{id}/restore', [RoomController::class, 'restore']);
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

Route::prefix('users')->group(function () {
    // Route cho quản lý User
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::patch('/{id}', [UserController::class, 'update']);
});
