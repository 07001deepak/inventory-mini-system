<?php

use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\OrderWebController;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Web\ProductWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// POS Counter Home
Route::get('/', [PosController::class, 'index'])->name('pos.index');

// Product Management (CRUD)
Route::resource('products', ProductWebController::class)->except(['create', 'edit', 'show']);

// Customer Management (CRUD)
Route::resource('customers', CustomerWebController::class)->except(['create', 'edit', 'show']);

// Order History & Receipt Invoice
Route::get('orders', [OrderWebController::class, 'index'])->name('orders.index');
Route::get('orders/{order}', [OrderWebController::class, 'show'])->name('orders.show');

// Interactive API List & Postman-style Tester
Route::get('api-list', [\App\Http\Controllers\Web\ApiListWebController::class, 'index'])->name('api-list.index');
