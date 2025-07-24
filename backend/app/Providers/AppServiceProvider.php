<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FirebaseAuth::class, function ($app) {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path(env('FIREBASE_CREDENTIALS')));
            return $firebase->createAuth();
        });

        $this->app->singleton(FakerGenerator::class, function () {
            return FakerFactory::create('vi_VN');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Lấy đường dẫn tương đối từ .env
        $googleCredentialsPath = env('GOOGLE_APPLICATION_CREDENTIALS');

        // Chuyển thành đường dẫn tuyệt đối
        $absolutePath = base_path($googleCredentialsPath);

        // Kiểm tra file tồn tại
        if (!file_exists($absolutePath)) {
            Log::error('Google Cloud credentials file not found at: ' . $absolutePath);
            throw new \Exception('Google Cloud credentials file not found at: ' . $absolutePath);
        }

        // Thiết lập biến môi trường
        putenv("GOOGLE_APPLICATION_CREDENTIALS=$absolutePath");

        View::composer('*', function ($view) {
            // ===========================
            // Notifications từ MySQL
            // ===========================
            $unreadCount = Notification::where('status', 'Chưa đọc')->count();
            $latestNotifications = Notification::latest()->take(3)->get();

            // ===========================
            // Messages chưa đọc từ Firestore
            // ===========================
            $unreadMessageCount = 0;
            $latestMessages = [];

            try {
                $firestore = (new Factory)->createFirestore();
                $db = $firestore->database();

                $messagesRef = $db->collection('messages');

                // Đếm chưa đọc (is_read = false)
                $unreadQuery = $messagesRef->where('is_read', '=', false);
                $unreadDocs = $unreadQuery->documents();
                $unreadMessageCount = $unreadDocs->size();

                // Lấy 3 message mới nhất
                $latestQuery = $messagesRef->orderBy('created_at', 'DESC')->limit(3);
                $latestDocs = $latestQuery->documents();

                foreach ($latestDocs as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $data['id'] = $doc->id(); // lưu ID
                        $latestMessages[] = $data;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error fetching messages from Firestore: ' . $e->getMessage());
            }

            $view->with([
                'unreadCount' => $unreadCount,
                'latestNotifications' => $latestNotifications,
                'unreadMessageCount' => $unreadMessageCount,
                'latestMessages' => $latestMessages,
            ]);
        });
    }
}
