<?php

use App\Http\Controllers\DistrictController;
use App\Http\Controllers\MotelController;
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