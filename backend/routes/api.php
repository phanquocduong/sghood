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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::put('/bookmarks/{id}', [BookmarkController::class, 'update']);
    Route::delete('/bookmarks/{id}', [BookmarkController::class, 'destroy']);
});
