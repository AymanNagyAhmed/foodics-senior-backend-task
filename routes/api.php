<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/products', App\Http\Controllers\ProductController::class);
Route::apiResource('/orders', App\Http\Controllers\OrderController::class);
Route::apiResource('/order_items', App\Http\Controllers\OrderItemController::class);

Route::post('/branches', [App\Http\Controllers\BranchController::class, 'store']);
Route::put('/branches/{id}', [App\Http\Controllers\BranchController::class, 'update']);
