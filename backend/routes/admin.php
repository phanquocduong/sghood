<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;


Route::prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.editUser');
    Route::put('/users/{id}/edit', [UserController::class, 'update'])->name('admin.updateUser');

});
