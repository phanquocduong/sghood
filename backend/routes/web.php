<?php

use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AmenityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Motel Routes Group
Route::prefix('motels')->name('motels.')->group(function () {
    Route::get('/', [MotelController::class, 'index'])->name('index');
    Route::get('/create', [MotelController::class, 'create'])->name('create');
    Route::post('/', [MotelController::class, 'store'])->name('store');
    Route::get('/trash', [MotelController::class, 'trash'])->name('trash');
    Route::get('/{id}', [MotelController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [MotelController::class, 'edit'])->name('edit');
    Route::put('/{id}', [MotelController::class, 'update'])->name('update');
    Route::delete('/{id}', [MotelController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore', [MotelController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [MotelController::class, 'forceDestroy'])->name('forceDelete');
});

// District Routes Group
Route::prefix('districts')->name('districts.')->group(function () {
    Route::get('/', [DistrictController::class, 'index'])->name('index');
    Route::get('/create', [DistrictController::class, 'create'])->name('create');
    Route::post('/', [DistrictController::class, 'store'])->name('store');
    Route::get('/trash', [DistrictController::class, 'trash'])->name('trash');
    Route::get('/{id}', [DistrictController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [DistrictController::class, 'edit'])->name('edit');
    Route::put('/{id}', [DistrictController::class, 'update'])->name('update');
    Route::delete('/{id}', [DistrictController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore', [DistrictController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [DistrictController::class, 'forceDestroy'])->name('forceDelete');
});

// Room Routes Group
Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('index');
    Route::get('/create', [RoomController::class, 'create'])->name('create');
    Route::post('/', [RoomController::class, 'store'])->name('store');
    Route::get('/trash', [RoomController::class, 'trash'])->name('trash');
    Route::get('/{id}', [RoomController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [RoomController::class, 'edit'])->name('edit');
    Route::match(['put', 'patch'], '/{id}', [RoomController::class, 'update'])->name('update');
    Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
    Route::get('/trash/{id}', [RoomController::class, 'showTrashed'])->name('showTrashed');
    Route::post('/restore/{id}', [RoomController::class, 'restore'])->name('restore');
    Route::delete('/force-delete/{id}', [RoomController::class, 'forceDelete'])->name('forceDelete');

    // Image management routes
    Route::post('/{roomId}/images/{imageId}/delete', [RoomController::class, 'deleteImage'])->name('image.delete');
});

// Amenity Routes Group
Route::prefix('amenities')->name('amenities.')->group(function () {
    Route::get('/', [AmenityController::class, 'index'])->name('index');
    Route::get('/create', [AmenityController::class, 'create'])->name('create');
    Route::post('/', [AmenityController::class, 'store'])->name('store');
    Route::get('/trash', [AmenityController::class, 'trash'])->name('trash');
    Route::get('/{id}', [AmenityController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [AmenityController::class, 'edit'])->name('edit');
    Route::match(['put', 'patch'], '/{id}', [AmenityController::class, 'update'])->name('update');
    Route::delete('/{id}', [AmenityController::class, 'destroy'])->name('destroy');
    Route::post('/restore/{id}', [AmenityController::class, 'restore'])->name('restore');
    Route::delete('/force-delete/{id}', [AmenityController::class, 'forceDelete'])->name('forceDelete');
});
