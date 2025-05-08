<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\FirebaseAuthController;

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->post('/firebase-auth', [FirebaseAuthController::class, 'auth']);
Route::post('/firebase-register', [FirebaseAuthController::class, 'register']);
Route::middleware('throttle:60,1')->post('/refresh-token', [FirebaseAuthController::class, 'refreshToken']);
Route::post('/logout', [FirebaseAuthController::class, 'logout']);

Route::middleware('firebase')->group(function () {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });
});

Route::prefix('bookmarks')->group(function () {
    Route::get('/', [BookmarkController::class, 'index']);
    Route::post('/', [BookmarkController::class, 'store']);
    Route::delete('/{id}', [BookmarkController::class, 'destroy']);
});
