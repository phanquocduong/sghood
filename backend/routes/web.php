<?php

use App\Http\Controllers\MotelController;
use App\Models\Motel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/motels', [MotelController::class, 'index'])->name('motels.index');
Route::get('/motels/create', [MotelController::class, 'create'])->name('motels.create');
Route::post('/motels', [MotelController::class, 'store'])->name('motels.store');
Route::get('/motels/1', [MotelController::class, 'show'])->name('motels.show');
Route::get('/motels/1/edit', [MotelController::class, 'edit'])->name('motels.edit');
Route::put('/motels/1', [MotelController::class, 'update'])->name('motels.update');
Route::delete('/motels/1', [MotelController::class, 'destroy'])->name('motels.destroy');

