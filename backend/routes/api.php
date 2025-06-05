<?php

use App\Http\Controllers\Apis\RoomController;
use App\Http\Controllers\Apis\AmenityController;
use App\Http\Controllers\Apis\AuthController;
use App\Http\Controllers\Apis\BookingController;
use App\Http\Controllers\Apis\DistrictController;
use App\Http\Controllers\Apis\MotelController;
use App\Http\Controllers\Apis\UserController;
use App\Http\Controllers\Apis\ViewingScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);
    Route::patch('/user/change-password', [UserController::class, 'changePassword']);

    Route::post('/viewing-schedules', [ViewingScheduleController::class, 'store']);
    Route::get('/viewing-schedules', [ViewingScheduleController::class, 'index']);
    Route::post('/viewing-schedules/{id}/reject', [ViewingScheduleController::class, 'reject']);

    Route::post('/booking', [BookingController::class, 'store']);
});

// Email verification routes
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// Redirect route for frontend
Route::get('/xac-minh-email', function (Request $request) {
    return redirect()->to('/xac-minh-email?' . http_build_query($request->query()));
})->name('verification.redirect');

Route::get('/districts', [DistrictController::class, 'index']);
Route::get('/motels/featured', [MotelController::class, 'featured']);
Route::get('/amenities', [AmenityController::class, 'index']);
Route::get('/motels/search', [MotelController::class, 'search']);
Route::get('/motels/{slug}', [MotelController::class, 'show']);
Route::get('/motels/{slug}/rooms/{roomId}', [RoomController::class, 'show']);
