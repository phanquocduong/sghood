<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RepairRequestController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StatisticController;
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
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CKEditorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContractExtensionController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContractTenantController;
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
Route::post('/save-fcm-token', [AuthController::class, 'saveFcmToken'])->name('save-fcm-token');
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf-token');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Các route được bảo vệ bởi middleware admin
Route::middleware('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [StatisticController::class, 'index'])->name('statistics');

    // Notes Routes Group
    Route::prefix('notes')->name('notes.')->group(function () {
        Route::get('/', [NoteController::class, 'index'])->name('index');
        Route::post('/', [NoteController::class, 'store'])->name('store');
        Route::post('/note-dashboard', [NoteController::class, 'storeDashboard'])->name('storeDashboard');
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
        Route::put('/{roomId}/confirm-repair', [RoomController::class, 'confirmRepair'])->name('confirmRepair');

        // Image management routes
        Route::post('/{roomId}/images/{imageId}/delete', [RoomController::class, 'deleteImage'])->name('image.delete');
    });

    // Amenity Routes Group
    Route::prefix('amenities')->name('amenities.')->group(function () {
        Route::get('/', [AmenityController::class, 'index'])->name('index');
        Route::get('/create', [AmenityController::class, 'create'])->name('create');
        Route::post('/', [AmenityController::class, 'store'])->name('store');
        Route::get('/trash', [AmenityController::class, 'trash'])->name('trash');
        Route::get('/change-order', [AmenityController::class, 'changeOrder'])->name('change-order');
        Route::post('/reorder', [AmenityController::class, 'reorder'])->name('reorder');
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
        Route::patch('/{id}/update-role', [UserController::class, 'updateRole'])->name('updateRole');
        Route::patch('/{id}/update-status', [UserController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/by-ids', [UserController::class, 'getByIds'])->name('byIds');
        Route::get('/{id}', [UserController::class, 'show'])->name('modal');
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
        Route::put('/{id}/confirm', [ScheduleController::class, 'confirm'])->name('schedules.confirm');
        Route::put('/{id}/complete', [ScheduleController::class, 'complete'])->name('schedules.complete');
    });

    // Contract routes group
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/contract-extensions', [ContractExtensionController::class, 'index'])->name('contract-extensions');
        Route::post('/contract-extensions/{id}/status', [ContractExtensionController::class, 'updateExtensionStatus'])->name('contract_extensions.update_status');
        Route::get('/{id}', [ContractController::class, 'show'])->name('show');
        Route::match(['put', 'patch'], '/{id}/update-status', [ContractController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{id}/terminate-early', [ContractController::class, 'terminateEarly'])->name('terminateEarly');
        Route::get('/{id}/download', [ContractController::class, 'download'])->name('download');
        Route::get('/{contractId}/identity-document/{imagePath}', [ContractController::class, 'showIdentityDocument'])->name('showIdentityDocument');
        Route::post('/{contract}/send-revision-email', [ContractController::class, 'sendRevisionEmail'])->name('sendRevisionEmail');
        Route::match(['put', 'patch'], '/{id}/content', [ContractController::class, 'updateContent'])->name('updateContent');
        Route::post('/{id}/reactivate', [ContractController::class, 'reactivate'])->name('reactivate');
        Route::delete('{id}/delete-identity', [ContractController::class, 'deleteIdentity'])->name('deleteIdentity');
    });

    // Quản lý người ở cùng
    Route::prefix('contract-tenants')->name('contract-tenants.')->group(function () {
        Route::get('/index', [ContractTenantController::class, 'index'])->name('index');
        Route::post('/update-status/{id}', [ContractTenantController::class, 'updateStatus'])->name('update-status');
        Route::get('/{tenantId}/identity-document/{imagePath}', [ContractTenantController::class, 'showTenantIdentityDocument'])
            ->name('identity-document');
    });

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    // Route notification for navbar
    Route::get('/notifications/header-data', [NotificationController::class, 'headerData'])
        ->name('notifications.header');

    // Message routes group
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::post('/send', [MessageController::class, 'sendMessage'])->name('send');
        Route::get('/chat-box', [MessageController::class, 'showChat'])->name('chat-box');
        Route::post('/mark-as-read', [MessageController::class, 'markAsRead'])->name('mark-as-read');
        Route::get('/header', [MessageController::class, 'header'])->name('header');
    });

    // Blog routes group
    Route::prefix('blogs')->name('blogs.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/store', [BlogController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [BlogController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [BlogController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [BlogController::class, 'delete'])->name('delete');
        Route::get('/trash', [BlogController::class, 'trash'])->name('trash');
        Route::patch('/restore/{id}', [BlogController::class, 'restore'])->name('restore');
        Route::delete('/force-delete/{id}', [BlogController::class, 'Forcedelete'])->name('force-delete');
        Route::get('/detail/{id}', [BlogController::class, 'showBlog'])->name('detail');
        Route::patch('/{id}/update-cate', [BlogController::class, 'updateCategory'])->name('updateCategory');

    });
    // Comment routes
    Route::prefix('blogs/{blogId}/comments')->name('comments.')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('index');
        Route::post('/reply', [CommentController::class, 'reply'])->name('reply');
        Route::post('/{id}/toggle-visibility', [CommentController::class, 'toggleVisibility'])->name('toggleVisibility');
    });
    Route::prefix('CKEditors')->name('ckeditors.')->group(function () {
        Route::post('/upload-image', [CKEditorController::class, 'upload'])->name('upload');
    });

    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::match(['put', 'patch'], '/{id}/status', [InvoiceController::class, 'updateStatus'])->name('updateStatus');
    });

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/revenue-by-year', [TransactionController::class, 'getRevenueByYear'])->name('getRevenueByYear');
        Route::get('/{id}', [TransactionController::class, 'show'])->name('show');
        Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('updateStatus');
    });

    Route::prefix('checkouts')->name('checkouts.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::get('/{id}', [CheckoutController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CheckoutController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{id}', [CheckoutController::class, 'update'])->name('update');
        Route::put('/{checkout}/re-inventory', [CheckoutController::class, 'reInventory'])->name('reInventory');
        Route::patch('/{id}/confirm', [CheckoutController::class, 'confirm'])->name('confirm');
        Route::patch('/{id}/force-confirm-user', [CheckoutController::class, 'forceConfirmUser'])->name('forceConfirmUser');
        Route::patch('/{id}/confirmLeft', [CheckoutController::class, 'confirmLeft'])->name('confirmLeft');
    });

    // Route for meter reading index
    Route::get('/meter-readings', [MeterReadingController::class, 'index'])->name('meter_readings.index');
    Route::put('/meter-readings', [MeterReadingController::class, 'store'])->name('meter_readings.store');
    Route::get('/meter-readings/filter', [MeterReadingController::class, 'filter'])->name('meter_readings.filter');
    Route::get('/meter-readings/history', [MeterReadingController::class, 'history'])->name('meter_readings.history');
    Route::get('/meter-readings/export', [MeterReadingController::class, 'export'])->name('meter_readings.export');



    // Route for repair requests
    Route::get('/repair-requests', [RepairRequestController::class, 'index'])->name('repair_requests.index');
    Route::put('/repair-requests/{id}/status', [RepairRequestController::class, 'updateStatus'])->name('repairs.updateStatus');
    Route::put('/repair-requests/{id}', [RepairRequestController::class, 'updateStatusDetail'])->name('repair_requests.updateStatusDetail');
    Route::get('/repair-requests/{id}', [RepairRequestController::class, 'show'])->name('repair_requests.show');
    Route::put('repair-requests/{repairRequest}/note', [RepairRequestController::class, 'updateNote'])
        ->name('repair_requests.updateNote');
});

// File tải file PDF hợp đồng
Route::get('/contract/pdf/{id}', function ($id) {
    $contract = Contract::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

    if ($contract->file && Storage::disk('private')->exists($contract->file)) {
        $filePath = Storage::disk('private')->path($contract->file);
        return response()->download($filePath, "contract-{$id}.pdf");
    }

    abort(404, 'File hợp đồng không tồn tại');
});
