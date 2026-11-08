<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\DietaryPreferenceController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\MerchantDashboardController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShopperController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Customer routes
    Route::get('/merchants', [MerchantController::class, 'index']);
    Route::get('/merchants/{merchant}', [MerchantController::class, 'show']);
    Route::get('/merchants/{merchant}/categories', [MerchantController::class, 'categories']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/health-filters', [ProductController::class, 'healthFilters']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{order}/rate', [OrderController::class, 'rate']);

    // Address routes
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    // Dietary preferences
    Route::get('/dietary-preferences', [DietaryPreferenceController::class, 'show']);
    Route::put('/dietary-preferences', [DietaryPreferenceController::class, 'update']);

    // Shopper routes
    Route::prefix('shopper')->middleware('role:shopper')->group(function () {
        Route::get('/dashboard', [ShopperController::class, 'dashboard']);
        Route::get('/batches/available', [ShopperController::class, 'availableBatches']);
        Route::get('/batches', [ShopperController::class, 'myBatches']);
        Route::post('/batches/{batch}/accept', [ShopperController::class, 'acceptBatch']);
        Route::put('/batches/{batch}/status', [ShopperController::class, 'updateBatchStatus']);
        Route::get('/earnings', [ShopperController::class, 'earnings']);
    });

    // Merchant routes
    Route::prefix('merchant')->middleware('role:merchant')->group(function () {
        Route::get('/dashboard', [MerchantDashboardController::class, 'dashboard']);
        Route::get('/orders', [MerchantDashboardController::class, 'orders']);
        Route::put('/orders/status', [MerchantDashboardController::class, 'updateOrderStatus']);
        Route::get('/products', [MerchantDashboardController::class, 'products']);
        Route::post('/products', [MerchantDashboardController::class, 'storeProduct']);
        Route::put('/products/{product}', [MerchantDashboardController::class, 'updateProduct']);
        Route::delete('/products/{product}', [MerchantDashboardController::class, 'destroyProduct']);
        Route::get('/analytics', [MerchantDashboardController::class, 'analytics']);
    });

    // Message routes (customer-shopper chat)
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/orders/{order}/messages', [MessageController::class, 'index']);
    Route::post('/orders/{order}/messages', [MessageController::class, 'store']);
    Route::put('/messages/{message}/read', [MessageController::class, 'markAsRead']);

    // Favorites routes
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/shoppers/{shopper}', [FavoriteController::class, 'toggleShopper']);
    Route::post('/favorites/merchants/{merchant}', [FavoriteController::class, 'toggleMerchant']);
    Route::post('/favorites/check', [FavoriteController::class, 'checkStatus']);
    Route::put('/favorites/{favorite}', [FavoriteController::class, 'updateNotes']);
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy']);
});
