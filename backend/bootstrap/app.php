<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors; // Thêm dòng này

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'firebase' => \App\Http\Middleware\FirebaseAuth::class
        ]);
        $middleware->append(HandleCors::class); // Thêm HandleCors vào global middleware
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
