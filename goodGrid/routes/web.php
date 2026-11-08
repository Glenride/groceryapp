<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth', 'verified', EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminDashboardController::class, 'showUser'])->name('users.show');
    Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
    Route::get('/orders', [AdminDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [AdminDashboardController::class, 'showOrder'])->name('orders.show');
    Route::get('/merchants', [AdminDashboardController::class, 'merchants'])->name('merchants');
    Route::put('/merchants/{merchant}/status', [AdminDashboardController::class, 'updateMerchantStatus'])->name('merchants.status');
});

require __DIR__.'/settings.php';
