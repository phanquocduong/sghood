<?php

use App\Http\Controllers\FirebaseAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/firebase-auth', [FirebaseAuthController::class, 'auth']);
Route::post('/firebase-register', [FirebaseAuthController::class, 'register']);
Route::post('/logout', [FirebaseAuthController::class, 'logout']);
Route::post('/refresh-token', [FirebaseAuthController::class, 'refreshToken']);

Route::middleware('firebase')->group(function () {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });
});

