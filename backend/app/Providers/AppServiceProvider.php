<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use App\Models\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Kreait\Firebase\Auth as FirebaseAuth;
use Dompdf\Font;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
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
            $unreadCount = Notification::where('status', 'Chưa đọc')->count();
            $latestNotifications = Notification::latest()->take(3)->get();

            $view->with('unreadCount', $unreadCount)
                ->with('latestNotifications', $latestNotifications);
        });

        // // Đăng ký font Times New Roman
        // $fontPath = storage_path('fonts/times-new-roman.ttf');
        // if (file_exists($fontPath)) {
        //     Font::register($fontPath, 'times-new-roman');
        // }

        // // Đặt font mặc định cho DomPDF
        // PDF::setOptions(['defaultFont' => 'times-new-roman']);
    }
}