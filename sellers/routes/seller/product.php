<?php

use Illuminate\Support\Facades\Route; 



Route::get('/admin/products', [App\Http\Controllers\AdminProductController::class, 'products']);
Route::get('/admin/product/{id}', [App\Http\Controllers\AdminProductController::class, 'product']);
Route::get('/admin/product/{id}/{type}', [App\Http\Controllers\AdminProductController::class, 'product']);
Route::get('/admin/product/{id}/{sellerId}/{type}', [App\Http\Controllers\AdminProductController::class, 'product']);
Route::get('/product/{id}/{sellerId}/{type}', [App\Http\Controllers\Seller\SellerProductController::class, 'product']);
Route::get('/adminProduct/{id}', [App\Http\Controllers\Seller\SellerProductController::class, 'adminProduct']);

Route::post('/admin/products', [App\Http\Controllers\AdminProductController::class, 'products']);
Route::post('/product/validate', [App\Http\Controllers\Seller\SellerProductController::class, 'validateProduct']);
Route::post('/product/save', [App\Http\Controllers\Seller\SellerProductController::class, 'saveProduct']);
Route::post('/product/updateStatus', [App\Http\Controllers\Seller\SellerProductController::class, 'updateStatus']);
Route::post('/product/associativeProducts', [App\Http\Controllers\Seller\SellerProductController::class, 'associativeProducts']);

Route::post('/editor-image', [App\Http\Controllers\Seller\SellerProductController::class, 'editorImage']);
Route::post('/delete/product/image', [App\Http\Controllers\Seller\SellerProductController::class, 'deletePrdImg']);

