<?php

use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
use App\Http\Controllers\RoomController;
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
// Route::get('/rooms/motel/{motelId}', [RoomController::class, 'roomsByMotel'])->name('rooms.byMotel');
