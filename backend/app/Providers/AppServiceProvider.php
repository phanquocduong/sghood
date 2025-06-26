<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Illuminate\Support\Facades\DB;

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
    public function boot(): void
    {
         DB::statement("SET time_zone='+07:00'");
    }
}
