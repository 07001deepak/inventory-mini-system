<?php

use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Version 1 - Prefixed with /v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Product API Endpoints
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::apiResource('/products', ProductController::class);

    // Customer API Endpoints & Order History by Email
    Route::get('/customers/orders', [CustomerController::class, 'orders'])->name('customers.orders');
    Route::apiResource('/customers', CustomerController::class);

    // Order API Endpoints
    Route::apiResource('/orders', OrderController::class)->only(['index', 'store', 'show']);
});
