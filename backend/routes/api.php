<?php

use App\Http\Controllers\Apis\RoomController;
use App\Http\Controllers\Apis\AmenityController;
use App\Http\Controllers\Apis\AuthController;
use App\Http\Controllers\Apis\BookingController;
use App\Http\Controllers\Apis\ContactController;
use App\Http\Controllers\Apis\DistrictController;
use App\Http\Controllers\Apis\MotelController;
use App\Http\Controllers\Apis\UserController;
use App\Http\Controllers\Apis\ConfigController;
use App\Http\Controllers\Apis\ContractController;
use App\Http\Controllers\Apis\InvoiceController;
use App\Http\Controllers\Apis\MessageController;
use App\Http\Controllers\Apis\NotificationController;
use App\Http\Controllers\Apis\RepairRequestController;
use App\Http\Controllers\Apis\SepayWebhookController;
use App\Http\Controllers\Apis\TransactionController;
use App\Http\Controllers\Apis\ViewingScheduleController;
use App\Http\Controllers\Apis\BlogController;
use App\Http\Controllers\Apis\CheckoutController;
use App\Http\Controllers\Apis\ContractExtensionController;
use App\Http\Controllers\Apis\RefundRequestController;
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
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/save-fcm-token', [UserController::class, 'saveFcmToken']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);
    Route::patch('/user/change-password', [UserController::class, 'changePassword']);

    Route::get('/schedules', [ViewingScheduleController::class, 'index']);
    Route::post('/schedules', [ViewingScheduleController::class, 'store']);
    Route::post('/schedules/{id}/reject', [ViewingScheduleController::class, 'reject']);

    Route::get('/motels/{motel}/rooms', [MotelController::class, 'getRooms']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{id}/reject', [BookingController::class, 'reject']);

    Route::get('/contracts', [ContractController::class, 'index']);
    Route::get('/contracts/{id}', [ContractController::class, 'show']);
    Route::post('/contracts/{id}/reject', [ContractController::class, 'reject']);
    Route::post('/extract-identity-images', [ContractController::class, 'extractIdentityImages']);
    Route::patch('/contracts/{id}', [ContractController::class, 'update']);
    Route::post('/contracts/{id}/sign', [ContractController::class, 'sign']);
    Route::get('/contracts/{id}/download-pdf', [ContractController::class, 'downloadPdf']);
    Route::post('/contracts/{id}/extend', [ContractController::class, 'extend']);
    Route::post('/contracts/{id}/return', [ContractController::class, 'requestReturn']);

    Route::get('/contract-extensions', [ContractExtensionController::class, 'index']);
    Route::post('/contract-extensions/{id}/reject', [ContractExtensionController::class, 'reject']);

    Route::get('/checkouts', [CheckoutController::class, 'index']);
    Route::post('/checkouts/{id}/reject', [CheckoutController::class, 'reject']);

    Route::get('/refund-requests', [RefundRequestController::class, 'index']);
    Route::post('/refund-requests/{id}/reject', [RefundRequestController::class, 'reject']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/months-years', [InvoiceController::class, 'getMonthsAndYears']);
    Route::get('/invoices/{code}', [InvoiceController::class, 'show']);
    Route::get('/invoices/{code}/status', [InvoiceController::class, 'checkStatus']);

    Route::get('/transactions', [TransactionController::class, 'index']);

    Route::get('/repair-requests', [RepairRequestController::class, 'index']);
    Route::post('/repair-requests', [RepairRequestController::class, 'store']);
    Route::patch('/repair-requests/{id}/cancel', [RepairRequestController::class, 'cancel']);
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
Route::post('/messages/send', [MessageController::class, 'sendMessage']);
Route::get('/messages/history/{userId}', [MessageController::class, 'getChatHistory']);
Route::get('/messages/conversations', [MessageController::class, 'getAdminConversations']);
Route::post('/messages/start-chat', [MessageController::class, 'startChat']);

// Blog Routes
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/show/{id}', [BlogController::class, 'showBlog']);


// Get all admin users
Route::get('/users/admins', [UserController::class, 'getAdmins']);

// Notification Routes
Route::prefix('notifications')->group(function () {
    Route::get('/user/{userId}', [NotificationController::class, 'getAllNotificationByUser'])->name('notifications.user');
    Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/{id}', [NotificationController::class, 'getByNotificationId'])->name('notifications.show');
});
