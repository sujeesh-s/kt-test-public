<?php

use Illuminate\Support\Facades\Route; 



Route::get('/sales/orders', [App\Http\Controllers\SalesOrderController::class, 'orders']);
Route::get('/sales/orders/{type}', [App\Http\Controllers\SalesOrderController::class, 'orders']);
Route::get('/sales/invoice/{id}', [App\Http\Controllers\SalesOrderController::class, 'invoice']);
Route::get('/sales/order/{id}', [App\Http\Controllers\SalesOrderController::class, 'order']);
Route::get('/sales/order/{id}/{type}', [App\Http\Controllers\SalesOrderController::class, 'order']);
Route::get('/sales/cancel/orders', [App\Http\Controllers\SalesOrderController::class, 'cancelOrders']);
Route::get('/sales/cancel/orders/{type}', [App\Http\Controllers\SalesOrderController::class, 'cancelOrders']);
Route::get('/sales/cancel/order/{id}', [App\Http\Controllers\SalesOrderController::class, 'cancelOrder']);
Route::get('/sales/return/orders', [App\Http\Controllers\SalesOrderController::class, 'returnOrders']);
Route::get('/sales/return/orders/{type}', [App\Http\Controllers\SalesOrderController::class, 'returnOrders']);
Route::get('/sales/return/order/{id}', [App\Http\Controllers\SalesOrderController::class, 'returnOrder']);

Route::post('/sales/orders', [App\Http\Controllers\SalesOrderController::class, 'orders']);
Route::post('/sales/orders/{type}', [App\Http\Controllers\SalesOrderController::class, 'orders']);
Route::post('/sales/order/updateStatus', [App\Http\Controllers\SalesOrderController::class, 'updateStatus']);
Route::post('/send/order/status/email', [App\Http\Controllers\SalesOrderController::class, 'orderStatusEmail']);
Route::post('/sales/cancel/orders', [App\Http\Controllers\SalesOrderController::class, 'cancelOrders']);
Route::post('/sales/cancel/orders/{type}', [App\Http\Controllers\SalesOrderController::class, 'cancelOrders']);
Route::post('/sales/return/orders', [App\Http\Controllers\SalesOrderController::class, 'returnOrders']);
Route::post('/sales/return/orders/{type}', [App\Http\Controllers\SalesOrderController::class, 'returnOrders']);
Route::post('/sales/order/return/updateStatus', [App\Http\Controllers\SalesOrderController::class, 'returnUpdateStatus']);


