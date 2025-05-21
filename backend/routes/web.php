<?php

use App\Http\Controllers\MotelController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Routes for Motels
Route::get('/motels', [MotelController::class, 'index'])->name('motels.index');
Route::get('/motels/create', [MotelController::class, 'create'])->name('motels.create');
Route::post('/motels', [MotelController::class, 'store'])->name('motels.store');
Route::get('/motels/{id}', [MotelController::class, 'show'])->name('motels.show');
Route::get('/motels/{id}/edit', [MotelController::class, 'edit'])->name('motels.edit');
Route::put('/motels/{id}', [MotelController::class, 'update'])->name('motels.update');
Route::delete('/motels/{id}', [MotelController::class, 'destroy'])->name('motels.destroy');

// Routes for Rooms
Route::post('/rooms/{roomId}/images/{imageId}/delete', [RoomController::class, 'deleteImage'])->name('rooms.image.delete');Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/rooms/{id}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
Route::match(['put', 'patch'], '/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');
Route::get('/rooms/trash', [RoomController::class, 'trash'])->name('rooms.trash');
Route::get('/rooms/trash/{id}', [RoomController::class, 'showTrashed'])->name('rooms.showTrashed');
Route::post('/rooms/restore/{id}', [RoomController::class, 'restore'])->name('rooms.restore');
Route::delete('/rooms/force-delete/{id}', [RoomController::class, 'forceDelete'])->name('rooms.forceDelete');
// Route::get('/rooms/motel/{motelId}', [RoomController::class, 'roomsByMotel'])->name('rooms.byMotel');
