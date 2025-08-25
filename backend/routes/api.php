<?php

use App\Http\Controllers\Apis\AmenityController;
use App\Http\Controllers\Apis\AuthController;
use App\Http\Controllers\Apis\BlogController;
use App\Http\Controllers\Apis\BookingController;
use App\Http\Controllers\Apis\CheckoutController;
use App\Http\Controllers\Apis\CommentController;
use App\Http\Controllers\Apis\ConfigController;
use App\Http\Controllers\Apis\ContactController;
use App\Http\Controllers\Apis\ContractController;
use App\Http\Controllers\Apis\ContractExtensionController;
use App\Http\Controllers\Apis\ContractTenantController;
use App\Http\Controllers\Apis\DistrictController;
use App\Http\Controllers\Apis\IdentityDocumentController;
use App\Http\Controllers\Apis\InvoiceController;
use App\Http\Controllers\Apis\MessageController;
use App\Http\Controllers\Apis\MotelController;
use App\Http\Controllers\Apis\NotificationController;
use App\Http\Controllers\Apis\RepairRequestController;
use App\Http\Controllers\Apis\SepayWebhookController;
use App\Http\Controllers\Apis\TransactionController;
use App\Http\Controllers\Apis\UserController;
use App\Http\Controllers\Apis\ViewingScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Các Route Xác Thực
|--------------------------------------------------------------------------
| Các route liên quan đến đăng ký, đăng nhập, xác minh email và đặt lại mật khẩu.
*/
Route::post('/register', [AuthController::class, 'register'])->name('auth.register'); // Đăng ký người dùng mới
Route::post('/login', [AuthController::class, 'login'])->name('auth.login'); // Đăng nhập người dùng
Route::patch('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.resetPassword'); // Đặt lại mật khẩu
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify'); // Xác minh email
Route::get('/xac-minh-email', function (Request $request) {
    return redirect()->to('/xac-minh-email?' . http_build_query($request->query()));
})->name('verification.redirect'); // Chuyển hướng xác minh email

/*
|--------------------------------------------------------------------------
| Các Route Yêu Cầu Xác Thực (auth:sanctum)
|--------------------------------------------------------------------------
| Các route yêu cầu người dùng đã đăng nhập, được bảo vệ bởi middleware auth:sanctum.
*/
Route::middleware('auth:sanctum')->group(function () {
    // Quản lý xác thực và thông tin người dùng
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout'); // Đăng xuất người dùng
    Route::get('/user', [AuthController::class, 'getUser'])->name('auth.getUser'); // Lấy thông tin người dùng hiện tại
    Route::post('/save-fcm-token', [UserController::class, 'saveFcmToken'])->name('user.saveFcmToken'); // Lưu FCM token cho thông báo đẩy
    Route::patch('/user/profile', [UserController::class, 'updateProfile'])->name('user.updateProfile'); // Cập nhật hồ sơ người dùng
    Route::patch('/user/change-password', [UserController::class, 'changePassword'])->name('user.changePassword'); // Đổi mật khẩu người dùng

    // Quản lý lịch xem nhà trọ
    Route::prefix('schedules')->controller(ViewingScheduleController::class)->group(function () {
        Route::get('/', 'index')->name('schedules.index'); // Lấy danh sách lịch xem
        Route::post('/', 'store')->name('schedules.store'); // Tạo mới lịch xem
        Route::patch('{id}', 'update')->name('schedules.update'); // Cập nhật lịch xem
        Route::patch('{id}/cancel', 'cancel')->name('schedules.cancel'); // Hủy lịch xem
    });

    // Quản lý đặt phòng
    Route::prefix('bookings')->controller(BookingController::class)->group(function () {
        Route::get('/', 'index')->name('bookings.index'); // Lấy danh sách đặt phòng
        Route::post('/', 'store')->name('bookings.store'); // Tạo mới đặt phòng
        Route::patch('/{id}/cancel', 'cancel')->name('bookings.cancel'); // Hủy đặt phòng
    });

    // Quản lý hợp đồng
    Route::prefix('contracts')->controller(ContractController::class)->group(function () {
        Route::get('/', 'index')->name('contracts.index'); // Lấy danh sách hợp đồng
        Route::get('/{id}', 'show')->name('contracts.show'); // Lấy chi tiết hợp đồng
        Route::patch('/{id}', 'update')->name('contracts.update'); // Cập nhật hợp đồng
        Route::patch('/{id}/sign', 'sign')->name('contracts.sign'); // Ký hợp đồng
        Route::patch('/{id}/cancel', 'cancel')->name('contracts.cancel'); // Hủy hợp đồng
        Route::patch('/{id}/early-termination', 'earlyTermination')->name('contracts.earlyTermination'); // Chấm dứt hợp đồng sớm
        Route::get('/{id}/download-pdf', 'downloadPdf')->name('contracts.downloadPdf'); // Tải PDF hợp đồng
    });

    // Quản lý người ở cùng trong hợp đồng
    Route::prefix('contracts/{contractId}/tenants')->controller(ContractTenantController::class)->group(function () {
        Route::get('/', 'index')->name('contractTenants.index'); // Lấy danh sách người ở cùng
        Route::post('/', 'store')->name('contractTenants.store'); // Thêm người ở cùng
        Route::patch('/{tenantId}/confirm', 'confirm')->name('contractTenants.confirm'); // Xác nhận người ở cùng
        Route::patch('/{tenantId}/cancel', 'cancel')->name('contractTenants.cancel'); // Hủy người ở cùng
    });

    // Quản lý gia hạn hợp đồng
    Route::post('/contracts/{id}/extend', [ContractExtensionController::class, 'extend'])->name('contractExtensions.extend'); // Gia hạn hợp đồng
    Route::prefix('contract-extensions')->controller(ContractExtensionController::class)->group(function () {
        Route::get('/', 'index')->name('contractExtensions.index'); // Lấy danh sách gia hạn hợp đồng
        Route::patch('/{id}/cancel', 'cancel')->name('contractExtensions.cancel'); // Hủy gia hạn hợp đồng
    });

    // Quản lý trả phòng
    Route::post('/contracts/{id}/return', [CheckoutController::class, 'requestReturn'])->name('checkouts.requestReturn'); // Yêu cầu trả phòng
    Route::prefix('checkouts')->controller(CheckoutController::class)->group(function () {
        Route::get('/', 'index')->name('checkouts.index'); // Lấy danh sách yêu cầu trả phòng
        Route::patch('/{id}/cancel', 'cancel')->name('checkouts.cancel'); // Hủy yêu cầu trả phòng
        Route::patch('/{id}/confirm', 'confirm')->name('checkouts.confirm'); // Xác nhận trả phòng
        Route::patch('/{id}/left-room', 'leftRoom')->name('checkouts.leftRoom'); // Xác nhận đã rời phòng
        Route::patch('/{id}/update-bank', 'updateBank')->name('checkouts.updateBank'); // Cập nhật thông tin ngân hàng
    });

    // Quản lý hóa đơn
    Route::prefix('invoices')->controller(InvoiceController::class)->group(function () {
        Route::get('/', 'index')->name('invoices.index'); // Lấy danh sách hóa đơn
        Route::get('/months-years', 'getMonthsAndYears')->name('invoices.getMonthsAndYears'); // Lấy danh sách tháng và năm của hóa đơn
        Route::get('/{code}', 'show')->name('invoices.show'); // Lấy chi tiết hóa đơn theo mã
        Route::get('/{code}/status', 'checkStatus')->name('invoices.checkStatus'); // Kiểm tra trạng thái hóa đơn
    });

    // Quản lý giao dịch
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index'); // Lấy danh sách giao dịch

    // Quản lý yêu cầu sửa chữa
    Route::prefix('repair-requests')->controller(RepairRequestController::class)->group(function () {
        Route::get('/', 'index')->name('repairRequests.index'); // Lấy danh sách yêu cầu sửa chữa
        Route::post('/', 'store')->name('repairRequests.store'); // Tạo mới yêu cầu sửa chữa
        Route::patch('/{id}/cancel', 'cancel')->name('repairRequests.cancel'); // Hủy yêu cầu sửa chữa
    });

    // Quản lý tin nhắn
    Route::prefix('messages')->controller(MessageController::class)->group(function () {
        Route::post('/send', 'sendMessage')->name('messages.send'); // Gửi tin nhắn
        Route::get('/history/{userId}', 'getChatHistory')->name('messages.history'); // Lấy lịch sử trò chuyện với người dùng
        Route::get('/conversations', 'getAdminConversations')->name('messages.conversations'); // Lấy danh sách cuộc trò chuyện của admin
        Route::post('/start-chat', 'startChat')->name('messages.startChat'); // Bắt đầu cuộc trò chuyện mới
    });

    // Quản lý giấy tờ tùy thân
    Route::post('/extract-identity-images', [IdentityDocumentController::class, 'extractIdentityImages'])->name('identityDocuments.extract'); // Trích xuất thông tin từ ảnh giấy tờ tùy thân

    // Quản lý thông báo
    Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
        Route::get('/user/{userId}', 'getAllNotificationByUser')->name('notifications.user'); // Lấy danh sách thông báo của người dùng
        Route::get('/{id}', 'getByNotificationId')->name('notifications.show'); // Lấy chi tiết thông báo theo ID
        Route::patch('/{id}/mark-as-read', 'markAsRead')->name('notifications.markAsRead'); // Đánh dấu thông báo đã đọc
        Route::patch('/mark-all-as-read', 'markAllAsRead')->name('notifications.markAllAsRead'); // Đánh dấu tất cả thông báo đã đọc
    });
});

/*
|--------------------------------------------------------------------------
| Các Route Công Khai
|--------------------------------------------------------------------------
| Các route không yêu cầu xác thực, dùng để truy cập thông tin công khai hoặc xử lý webhook.
*/

// Webhook SePay
Route::post('/sepay/webhook', [SepayWebhookController::class, 'handleWebhook'])->name('sepay.webhook'); // Xử lý webhook từ SePay

// Quản lý quận/huyện, nhà trọ, tiện ích và tìm kiếm
Route::get('/districts', [DistrictController::class, 'index'])->name('districts.index'); // Lấy danh sách quận/huyện
Route::get('/amenities', [AmenityController::class, 'index'])->name('amenities.index'); // Lấy danh sách tiện ích
Route::get('/motels/featured', [MotelController::class, 'featured'])->name('motels.featured'); // Lấy danh sách nhà trọ nổi bật
Route::get('/motels/search', [MotelController::class, 'search'])->name('motels.search'); // Tìm kiếm nhà trọ
Route::get('/motels/{slug}', [MotelController::class, 'show'])->name('motels.show'); // Lấy chi tiết nhà trọ theo slug
Route::get('/motels/{motel}/rooms', [MotelController::class, 'getRooms'])->name('motels.rooms'); // Lấy danh sách phòng của nhà trọ

// Quản lý liên hệ và cấu hình
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send'); // Gửi thông tin liên hệ
Route::get('/configs', [ConfigController::class, 'index'])->name('configs.index'); // Lấy danh sách cấu hình

// Quản lý blog
Route::prefix('blogs')->controller(BlogController::class)->group(function () {
    Route::get('/', 'index')->name('blogs.index'); // Lấy danh sách blog
    Route::get('/show/{slug}', 'showBlog')->name('blogs.show'); // Lấy chi tiết blog theo slug
    Route::get('/{id}/related', 'related')->name('blogs.related'); // Lấy danh sách blog liên quan
    Route::get('/popular', 'popular')->name('blogs.popular'); // Lấy danh sách blog phổ biến
    Route::post('/{id}/increase-view', 'increaseView')->name('blogs.increaseView'); // Tăng lượt xem blog
});

// Quản lý bình luận blog
Route::prefix('blogs')->controller(CommentController::class)->group(function () {
    Route::get('/{slug}/comments', 'getCommentsByBlog')->name('comments.index'); // Lấy danh sách bình luận của blog
    Route::post('/{blog}/send-comment', 'SendComment')->name('comments.send'); // Gửi bình luận mới
    Route::post('/{blog}/replay-comment', 'ReplayComment')->name('comments.reply'); // Trả lời bình luận
    Route::put('/comments/{id}', 'editComment')->name('comments.edit'); // Sửa bình luận
    Route::delete('/comments/{id}', 'deleteComment')->name('comments.delete'); // Xóa bình luận
    Route::post('/comments/{comment}/reaction', 'react')->name('comments.react'); // Thêm phản ứng cho bình luận
});

// Quản lý người dùng
Route::get('/users/admins', [UserController::class, 'getAdmins'])->name('users.getAdmins'); // Lấy danh sách quản trị viên
