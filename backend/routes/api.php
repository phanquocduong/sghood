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
use App\Http\Controllers\Apis\InvoiceController;
use App\Http\Controllers\Apis\NotificationController;
use App\Http\Controllers\Apis\ScheduleBookingController;
use App\Http\Controllers\Apis\SepayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::get('/xac-minh-email', function (Request $request) {
    return redirect()->to('/xac-minh-email?' . http_build_query($request->query()));
})->name('verification.redirect');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/save-fcm-token', [UserController::class, 'saveFcmToken']);
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
    Route::patch('/contracts/{id}', [ContractController::class, 'update']);

    Route::post('/contracts/{id}/sign', [ContractController::class, 'sign']);
    Route::get('/invoices/{code}/status', [InvoiceController::class, 'checkStatus']);
    Route::get('/contracts/{id}/download-pdf', [ContractController::class, 'downloadPdf']);


    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/months-years', [InvoiceController::class, 'getMonthsAndYears']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
});

Route::post('/sepay/webhook', [SepayWebhookController::class, 'handleWebhook']);


Route::get('/districts', [DistrictController::class, 'index']);
Route::get('/motels/featured', [MotelController::class, 'featured']);
Route::get('/amenities', [AmenityController::class, 'index']);
Route::get('/motels/search', [MotelController::class, 'search']);
Route::get('/motels/{slug}', [MotelController::class, 'show']);
Route::get('/motels/{slug}/rooms/{roomId}', [RoomController::class, 'show']);

// Contact Routes
Route::post('/contact', [ContactController::class, 'send']);
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
