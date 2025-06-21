<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use App\Models\Notification;
use Illuminate\Support\Facades\View;
use Kreait\Firebase\Auth as FirebaseAuth;

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
        View::composer('*', function ($view) {
            $unreadCount = Notification::where('status', 'Chưa đọc')->count();
            $latestNotifications = Notification::latest()->take(3)->get();

            $view->with('unreadCount', $unreadCount)
                ->with('latestNotifications', $latestNotifications);
        });
    }
}
