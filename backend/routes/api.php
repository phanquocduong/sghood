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

    Route::get('/contracts', [ContractController::class, 'index']);
    Route::get('/contracts/{id}', [ContractController::class, 'show']);
    Route::post('/contracts/{id}/reject', [ContractController::class, 'reject']);

    Route::post('/extract-identity-images', [ContractController::class, 'extractIdentityImages']);
    Route::post('/contracts/{id}/save', [ContractController::class, 'save']);

    Route::post('/save-fcm-token', [UserController::class, 'saveFcmToken']);
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

// Message Routes
Route::post('/messages/send', [\App\Http\Controllers\Apis\MessageController::class, 'sendMessage']);
Route::get('/messages/history/{userId}', [\App\Http\Controllers\Apis\MessageController::class, 'getChatHistory']);
Route::get('/messages/conversations', [\App\Http\Controllers\Apis\MessageController::class, 'getAdminConversations']);
Route::post('/messages/start-chat', [\App\Http\Controllers\Apis\MessageController::class, 'startChat']);
// Get all admin users
Route::get('/users/admins', [UserController::class, 'getAdmins']);

// Notification Routes
Route::prefix('notifications')->group(function () {
    Route::get('/user/{userId}', [NotificationController::class, 'getAllNotificationByUser'])->name('notifications.user');
    Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');  // API mark as read: http://localhost:8000/api/notifications/{userID}/mark-as-read
    Route::get('/{id}', [NotificationController::class, 'getByNotificationId'])->name('notifications.show');
});
