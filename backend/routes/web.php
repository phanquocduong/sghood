<?php

use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AmenityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// motel routes
Route::get('/motels', [MotelController::class, 'index'])->name('motels.index');
Route::get('/motels/create', [MotelController::class, 'create'])->name('motels.create');
Route::post('/motels', [MotelController::class, 'store'])->name('motels.store');
Route::get('/motels/trash', [MotelController::class, 'trash'])->name('motels.trash');
Route::get('/motels/{id}', [MotelController::class, 'show'])->name('motels.show');
Route::get('/motels/{id}/edit', [MotelController::class, 'edit'])->name('motels.edit');
Route::put('/motels/{id}', [MotelController::class, 'update'])->name('motels.update');
Route::delete('/motels/{id}', [MotelController::class, 'destroy'])->name('motels.destroy');
Route::post('/motels/{id}/restore', [MotelController::class, 'restore'])->name('motels.restore');
Route::delete('/motels/{id}/force-delete', [MotelController::class, 'forceDestroy'])->name('motels.forceDelete');


// dicstrict routes
Route::get('/districts', [DistrictController::class, 'index'])->name('districts.index');
Route::get('/districts/create', [DistrictController::class, 'create'])->name('districts.create');
Route::post('/districts', [DistrictController::class, 'store'])->name('districts.store');
Route::get('/districts/trash', [DistrictController::class, 'trash'])->name('districts.trash');
Route::get('/districts/{id}', [DistrictController::class, 'show'])->name('districts.show');
Route::get('/districts/{id}/edit', [DistrictController::class, 'edit'])->name('districts.edit');
Route::put('/districts/{id}', [DistrictController::class, 'update'])->name('districts.update');
Route::delete('/districts/{id}', [DistrictController::class, 'destroy'])->name('districts.destroy');
Route::post('/districts/{id}/restore', [DistrictController::class, 'restore'])->name('districts.restore');
Route::delete('/districts/{id}/force-delete', [DistrictController::class, 'forceDestroy'])->name('districts.forceDelete');

// Routes for Rooms
Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
Route::get('/rooms/trash', [RoomController::class, 'trash'])->name('rooms.trash');
Route::post('/rooms/{roomId}/images/{imageId}/delete', [RoomController::class, 'deleteImage'])->name('rooms.image.delete');Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/rooms/{id}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
Route::match(['put', 'patch'], '/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');
Route::get('/rooms/trash/{id}', [RoomController::class, 'showTrashed'])->name('rooms.showTrashed');
Route::post('/rooms/restore/{id}', [RoomController::class, 'restore'])->name('rooms.restore');
Route::delete('/rooms/force-delete/{id}', [RoomController::class, 'forceDelete'])->name('rooms.forceDelete');

// Route for Amenities
Route::get('/amenities', [AmenityController::class, 'index'])->name('amenities.index');
Route::get('/amenities/create', [AmenityController::class, 'create'])->name('amenities.create');
Route::post('/amenities', [AmenityController::class, 'store'])->name('amenities.store');
Route::get('/amenities/trash', [AmenityController::class, 'trash'])->name('amenities.trash');
Route::get('/amenities/{id}', [AmenityController::class, 'show'])->name('amenities.show');
Route::get('/amenities/{id}/edit', [AmenityController::class, 'edit'])->name('amenities.edit');
Route::match(['put', 'patch'], '/amenities/{id}', [AmenityController::class, 'update'])->name('amenities.update');
Route::delete('/amenities/{id}', [AmenityController::class, 'destroy'])->name('amenities.destroy');
Route::post('/amenities/restore/{id}', [AmenityController::class, 'restore'])->name('amenities.restore');
Route::delete('/amenities/force-delete/{id}', [AmenityController::class, 'forceDelete'])->name('amenities.forceDelete');
