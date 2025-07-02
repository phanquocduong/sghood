<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\MessageController;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Các route được bảo vệ bởi middleware admin
Route::middleware('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notes Routes Group
    Route::prefix('notes')->name('notes.')->group(function () {
        Route::get('/', [NoteController::class, 'index'])->name('index');
        Route::post('/', [NoteController::class, 'store'])->name('store');
        Route::delete('/{id}', [NoteController::class, 'destroy'])->name('destroy');
        Route::get('/users', [NoteController::class, 'getUsersWithNotes'])->name('users');
    });

    // Motel Routes Group
    Route::prefix('motels')->name('motels.')->group(function () {
        Route::get('/', [MotelController::class, 'index'])->name('index');
        Route::get('/create', [MotelController::class, 'create'])->name('create');
        Route::post('/', [MotelController::class, 'store'])->name('store');
        Route::get('/trash', [MotelController::class, 'trash'])->name('trash');
        Route::get('/{id}', [MotelController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [MotelController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MotelController::class, 'update'])->name('update');
        Route::delete('/{id}', [MotelController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [MotelController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [MotelController::class, 'forceDestroy'])->name('forceDelete');
        Route::post('/{motel_id}/images/{image_id}/delete', [MotelController::class, 'deleteMotelImage'])->name('motels.delete-image');
    });

    // District Routes Group
    Route::prefix('districts')->name('districts.')->group(function () {
        Route::get('/', [DistrictController::class, 'index'])->name('index');
        Route::get('/create', [DistrictController::class, 'create'])->name('create');
        Route::post('/', [DistrictController::class, 'store'])->name('store');
        Route::get('/trash', [DistrictController::class, 'trash'])->name('trash');
        Route::get('/{id}/edit', [DistrictController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DistrictController::class, 'update'])->name('update');
        Route::delete('/{id}', [DistrictController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [DistrictController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [DistrictController::class, 'forceDestroy'])->name('forceDelete');
    });

    // Room Routes Group
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::get('/create', [RoomController::class, 'create'])->name('create');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/trash', [RoomController::class, 'trash'])->name('trash');
        Route::get('/{id}', [RoomController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [RoomController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{id}', [RoomController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
        Route::get('/trash/{id}', [RoomController::class, 'showTrashed'])->name('showTrashed');
        Route::post('/restore/{id}', [RoomController::class, 'restore'])->name('restore');
        Route::delete('/force-delete/{id}', [RoomController::class, 'forceDelete'])->name('forceDelete');

        // Image management routes
        Route::post('/{roomId}/images/{imageId}/delete', [RoomController::class, 'deleteImage'])->name('image.delete');
    });

    // Amenity Routes Group
    Route::prefix('amenities')->name('amenities.')->group(function () {
        Route::get('/', [AmenityController::class, 'index'])->name('index');
        Route::get('/create', [AmenityController::class, 'create'])->name('create');
        Route::post('/', [AmenityController::class, 'store'])->name('store');
        Route::get('/trash', [AmenityController::class, 'trash'])->name('trash');
        Route::get('/{id}', [AmenityController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AmenityController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{id}', [AmenityController::class, 'update'])->name('update');
        Route::delete('/{id}', [AmenityController::class, 'destroy'])->name('destroy');
        Route::post('/restore/{id}', [AmenityController::class, 'restore'])->name('restore');
        Route::delete('/force-delete/{id}', [AmenityController::class, 'forceDelete'])->name('forceDelete');
    });

    // User Routes Group
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('editUser');
        Route::put('/{id}/edit', [UserController::class, 'update'])->name('updateUser');
    });

    // Booking Routes Group
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::patch('/{id}/update-status', [BookingController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{id}/update-note', [BookingController::class, 'updateCancellation_reason'])->name('updateNote');
    });

    // Config routes
    Route::prefix('configs')->name('configs.')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::get('/create', [ConfigController::class, 'create'])->name('create');
        Route::post('/', [ConfigController::class, 'store'])->name('store');
        Route::get('/trash', [ConfigController::class, 'trash'])->name('trash');
        Route::get('/{id}/edit', [ConfigController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ConfigController::class, 'update'])->name('update');
        Route::delete('/{id}', [ConfigController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/restore', [ConfigController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [ConfigController::class, 'forceDelete'])->name('forceDelete');
    });

    // schedule routes group
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::match(['put', 'patch'], '/{id}', [ScheduleController::class, 'updateStatus'])->name('updateStatus');
    });

    // Contract routes group
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/{id}', [ContractController::class, 'show'])->name('show');
        Route::match(['put', 'patch'], '/{id}/update-status', [ContractController::class, 'updateStatus'])->name('updateStatus');
        Route::get('/{id}/download', [ContractController::class, 'download'])->name('download');
    });

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    // Route notification for navbar
    Route::get('/notifications/header-data', [App\Http\Controllers\NotificationController::class, 'headerData'])
        ->name('notifications.header');

    // Message routes group
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::post('/send', [MessageController::class, 'sendMessage'])->name('send');
    });

    Route::get('/contracts/{contractId}/identity-document/{imagePath}', [ContractController::class, 'showIdentityDocument'])
        ->name('contracts.showIdentityDocument');
});

// File PDF
Route::get('/contract/pdf/{id}', function ($id) {
    $contract = Contract::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

    if ($contract->file && Storage::disk('private')->exists($contract->file)) {
        $filePath = Storage::disk('private')->path($contract->file);
        return response()->download($filePath, "contract-{$id}.pdf");
    }

    abort(404, 'File không tồn tại');
});

// Route for meter reading index
Route::get('/meter-readings', [MeterReadingController::class, 'index'])->name('meter_readings.index');
Route::put('/meter-readings', [MeterReadingController::class, 'store'])->name('meter_readings.store');
Route::get('/filter-meter-readings', [MeterReadingController::class, 'filter'])->name('meter_readings.filter');
