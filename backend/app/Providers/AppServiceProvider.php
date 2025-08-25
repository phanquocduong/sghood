<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

/**
 * Nhà cung cấp dịch vụ chính của ứng dụng, thiết lập các dịch vụ và cấu hình toàn cục.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký các dịch vụ của ứng dụng.
     */
    public function register(): void
    {
        // Đăng ký dịch vụ Firebase Authentication làm singleton
        $this->app->singleton(FirebaseAuth::class, function ($app) {
            // Tạo instance Firebase với thông tin xác thực từ file cấu hình
            $firebase = (new Factory)
                ->withServiceAccount(storage_path(env('FIREBASE_CREDENTIALS')));
            return $firebase->createAuth();
        });
    }

    /**
     * Khởi tạo các dịch vụ và cấu hình của ứng dụng.
     */
    public function boot(): void
    {
        // Lấy đường dẫn tương đối đến file thông tin xác thực Google Cloud từ cấu hình
        $googleCredentialsPath = config('services.firebase.credentials');

        // Chuyển đổi thành đường dẫn tuyệt đối
        $absolutePath = base_path($googleCredentialsPath);

        // Kiểm tra sự tồn tại của file thông tin xác thực
        if (!file_exists($absolutePath)) {
            // Ghi log lỗi nếu file không tồn tại
            Log::error('Google Cloud credentials file not found at: ' . $absolutePath);
            // Ném ngoại lệ nếu file không tồn tại
            throw new \Exception('Google Cloud credentials file not found at: ' . $absolutePath);
        }

        // Thiết lập biến môi trường GOOGLE_APPLICATION_CREDENTIALS
        putenv("GOOGLE_APPLICATION_CREDENTIALS=$absolutePath");

        // Cấu hình View Composer để chia sẻ dữ liệu với tất cả các view
        View::composer('*', function ($view) {
            // ===========================
            // Lấy thông báo từ MySQL
            // ===========================
            // Đếm số thông báo chưa đọc
            $unreadCount = Notification::where('status', 'Chưa đọc')->count();
            // Lấy 3 thông báo mới nhất
            $latestNotifications = Notification::latest()->take(3)->get();

            // ===========================
            // Lấy tin nhắn chưa đọc từ Firestore
            // ===========================
            $unreadMessageCount = 0; // Số tin nhắn chưa đọc
            $latestMessages = []; // Danh sách tin nhắn mới nhất

            try {
                // Khởi tạo Firestore
                $firestore = (new Factory)->createFirestore();
                $db = $firestore->database();

                // Tham chiếu đến collection messages trong Firestore
                $messagesRef = $db->collection('messages');

                // Lấy ID của admin hiện tại
                $adminId = Auth::id();

                // Đếm số tin nhắn chưa đọc gửi tới admin
                $unreadQuery = $messagesRef
                    ->where('is_read', '=', false) // Tin nhắn chưa đọc
                    ->where('receiver_id', '=', $adminId); // Gửi tới admin
                $unreadDocs = $unreadQuery->documents();
                $unreadMessageCount = $unreadDocs->size();

                // Lấy 3 tin nhắn mới nhất gửi tới admin
                $latestQuery = $messagesRef
                    ->where('receiver_id', '=', $adminId) // Gửi tới admin
                    ->orderBy('createdAt', 'DESC') // Sắp xếp theo thời gian tạo giảm dần
                    ->limit(3); // Giới hạn 3 tin nhắn
                $latestDocs = $latestQuery->documents();

                // Xử lý dữ liệu tin nhắn
                foreach ($latestDocs as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $data['id'] = $doc->id(); // Thêm ID Firestore vào dữ liệu
                        $latestMessages[] = $data;
                    }
                }
            } catch (\Throwable $e) {
                // Ghi log lỗi nếu truy vấn Firestore thất bại
                Log::error('Error fetching messages from Firestore: ' . $e->getMessage());
            }

            // Chia sẻ dữ liệu với tất cả các view
            $view->with([
                'unreadCount' => $unreadCount, // Số thông báo chưa đọc
                'latestNotifications' => $latestNotifications, // 3 thông báo mới nhất
                'unreadMessageCount' => $unreadMessageCount, // Số tin nhắn chưa đọc
                'latestMessages' => $latestMessages, // 3 tin nhắn mới nhất
            ]);
        });
    }
}
