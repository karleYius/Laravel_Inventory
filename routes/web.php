<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Public page: shows the Login / Register links for guests.
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Everything inside this group requires the user to be logged in.
Route::middleware('auth')->group(function () {

    // Your inventory dashboard (moved from "/" to "/dashboard").
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Product CRUD (your existing routes, unchanged).
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::middleware('admin')->group(function () {
        Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/product', [ProductController::class, 'store'])->name('product.store');
        Route::get('/product/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
        Route::put('/product/{product}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
    Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

    // Breeze profile pages.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Breeze's routes: /login, /register, /forgot-password, /reset-password/{token}, /logout, ...
require __DIR__.'/auth.php';