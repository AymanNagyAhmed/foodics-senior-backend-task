<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\RateLimitCreateOrder;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/products', App\Http\Controllers\ProductController::class);

Route::post('/orders', [OrderController::class, 'store'])->middleware(RateLimitCreateOrder::class);
Route::apiResource('/orders', OrderController::class)->except(['store']);

Route::apiResource('/order_items', App\Http\Controllers\OrderItemController::class);
