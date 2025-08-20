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
use App\Http\Controllers\Apis\CommentController;
use App\Http\Controllers\Apis\ContractTenantController;
use App\Http\Controllers\Apis\IdentityDocumentController;
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

    Route::prefix('schedules')->controller(ViewingScheduleController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::patch('{id}/cancel', 'cancel');
        Route::patch('{id}', 'update');
    });

    Route::get('/motels/{motel}/rooms', [MotelController::class, 'getRooms']);

    Route::prefix('bookings')->controller(BookingController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::post('/{id}/cancel', 'cancel');
    });

    Route::post('/extract-identity-images', [IdentityDocumentController::class, 'extractIdentityImages']);

    Route::prefix('contracts')->controller(ContractController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/{id}/cancel', 'cancel');
        Route::patch('/{id}', 'update');
        Route::post('/{id}/sign', 'sign');
        Route::get('/{id}/download-pdf', 'downloadPdf');
        Route::post('/{id}/early-termination', 'earlyTermination');

        Route::get('{contractId}/tenants', [ContractTenantController::class, 'index']);
        Route::post('{contractId}/tenants', [ContractTenantController::class, 'store']);
        Route::post('{contractId}/tenants/{tenantId}/cancel', [ContractTenantController::class, 'cancel']);
        Route::post('{contractId}/tenants/{tenantId}/confirm', [ContractTenantController::class, 'confirm']);
    });

    Route::post('/contracts/{id}/extend', [ContractExtensionController::class, 'extend']);
    Route::prefix('contract-extensions')->controller(ContractExtensionController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{id}/cancel', 'cancel');
    });

    Route::post('/contracts/{id}/return', [CheckoutController::class, 'requestReturn']);
    Route::prefix('checkouts')->controller(CheckoutController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{id}/cancel', 'cancel');
        Route::post('/{id}/confirm', 'confirm');
        Route::post('/{id}/left-room', 'leftRoom');
        Route::post('/{id}/update-bank', 'updateBank');
    });

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
Route::get('/show/{slug}', [BlogController::class, 'showBlog']);
Route::get('/blogs/{id}/related', [BlogController::class, 'related']);
Route::get('/blogs/popular', [BlogController::class, 'popular']);
Route::post('/blogs/{id}/increase-view', [BlogController::class, 'increaseView']);

// Comment blogs route
Route::get('/blogs/{slug}/comments', [CommentController::class, 'getCommentsByBlog']);
Route::post('/blogs/{blog}/send-comment', [CommentController::class, 'SendComment']);
Route::post('/blogs/{blog}/replay-comment', [CommentController::class, 'ReplayComment']);
Route::put('/comments/{id}', [CommentController::class, 'editComment']);
Route::delete('/comments/{id}', [CommentController::class, 'deleteComment']);
Route::post('/comments/{comment}/reaction', [CommentController::class, 'react']);

// Get all admin users
Route::get('/users/admins', [UserController::class, 'getAdmins']);
Route::post('/users/by-ids', [UserController::class, 'getByIds']);

// Notification Routes
Route::prefix('notifications')->group(function () {
    Route::get('/user/{userId}', [NotificationController::class, 'getAllNotificationByUser'])->name('notifications.user');
    Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/{id}', [NotificationController::class, 'getByNotificationId'])->name('notifications.show');
});
