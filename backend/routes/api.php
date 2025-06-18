<?php

use App\Http\Controllers\Apis\RoomController;
use App\Http\Controllers\Apis\AmenityController;
use App\Http\Controllers\Apis\AuthController;
use App\Http\Controllers\Apis\ContactController;
use App\Http\Controllers\Apis\DistrictController;
use App\Http\Controllers\Apis\MotelController;
use App\Http\Controllers\Apis\UserController;
use App\Http\Controllers\Apis\ConfigController;
use App\Http\Controllers\Apis\ContractController;
use App\Http\Controllers\Apis\OcrController;
use App\Http\Controllers\Apis\NotificationController;
use App\Http\Controllers\Apis\ScheduleBookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);
    Route::patch('/user/change-password', [UserController::class, 'changePassword']);

    Route::get('/schedules-bookings', [ScheduleBookingController::class, 'index']);
    Route::post('/schedules-bookings', [ScheduleBookingController::class, 'store']);
    Route::post('/schedules-bookings/{id}/{type}/reject', [ScheduleBookingController::class, 'reject']);
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

// Contact Routes
Route::post('/contact', [ContactController::class, 'send']);
// Get all config
Route::get('/configs', [ConfigController::class, 'index']);
Route::post('/extract-cccd', [OcrController::class, 'extractCccdData']);

Route::get('/users/{userId}/contract', [ContractController::class, 'getContractsByUser']);

Route::get('/users/{userId}/notifications', [NotificationController::class, 'getAllNotificationByUser']);
Route::get('/notifications/{id}', [NotificationController::class, 'getByNotificationId']);

// Message Routes
Route::post('/messages/send', [\App\Http\Controllers\Apis\MessageController::class, 'sendMessage']);
Route::get('/messages/history/{userId}', [\App\Http\Controllers\Apis\MessageController::class, 'getChatHistory']);
Route::get('/messages/conversations', [\App\Http\Controllers\Apis\MessageController::class, 'getAdminConversations']);
