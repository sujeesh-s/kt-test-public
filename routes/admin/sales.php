<?php

use Illuminate\Support\Facades\Route; 



Route::get('/admin/sales/orders', [App\Http\Controllers\Admin\SalesOrderController::class, 'orders']);
Route::get('/admin/sales/orders/{type}', [App\Http\Controllers\Admin\SalesOrderController::class, 'orders']);
Route::get('/admin/sales/order/{id}', [App\Http\Controllers\Admin\SalesOrderController::class, 'order']);
Route::get('/admin/sales/order/{id}/{type}', [App\Http\Controllers\Admin\SalesOrderController::class, 'order']);
Route::get('/admin/sales/invoice/{id}', [App\Http\Controllers\Admin\SalesOrderController::class, 'invoice']);
Route::get('/admin/sales/cancel/orders', [App\Http\Controllers\Admin\SalesOrderController::class, 'cancelOrders']);
Route::get('/admin/sales/cancel/orders/{type}', [App\Http\Controllers\Admin\SalesOrderController::class, 'cancelOrders']);
Route::get('/sales/cancel/order/{id}', [App\Http\Controllers\Admin\SalesOrderController::class, 'cancelOrder']);


Route::post('/admin/sales/orders', [App\Http\Controllers\Admin\SalesOrderController::class, 'orders']);
Route::post('/admin/sales/orders/{type}', [App\Http\Controllers\Admin\SalesOrderController::class, 'orders']);
Route::post('/admin/sales/order/updateStatus', [App\Http\Controllers\Admin\SalesOrderController::class, 'updateStatus']);
Route::post('/admin/send/order/status/email', [App\Http\Controllers\Admin\SalesOrderController::class, 'orderStatusEmail']);
Route::post('/admin/sales/cancel/orders', [App\Http\Controllers\Admin\SalesOrderController::class, 'cancelOrders']);
Route::post('/admin/sales/cancel/orders/{type}', [App\Http\Controllers\Admin\SalesOrderController::class, 'cancelOrders']);

Route::get('/admin/sales/refund/order/request', [App\Http\Controllers\Admin\SalesOrderController::class, 'refundOrders']);
Route::get('/admin/sales/refund/{id}/{type}', [App\Http\Controllers\Admin\SalesOrderController::class, 'refund']);
Route::post('/admin/sales/order/refund/updateStatus', [App\Http\Controllers\Admin\SalesOrderController::class, 'refundupdateStatus']);

