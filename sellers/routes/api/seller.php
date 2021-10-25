<?php

use Illuminate\Support\Facades\Route;

Route::post('/seller/version', [App\Http\Controllers\Api\seller\AppSettings::class, 'version_check']);

Route::post('/country', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'get_country']);
Route::post('/state', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'get_state']);
Route::post('/city', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'get_city']);
Route::post('/category', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'get_categories']);


Route::post('/seller/login', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'login']);
Route::post('/seller/login-username', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'login_username']);
Route::post('/seller/register', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'register']);
Route::post('/seller/check-email', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'check_username']);
Route::post('/seller/forgot-password', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'forgot_pwd']);
Route::post('/seller/generate-otp', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'generate_seller_otp']);
Route::post('/seller/confirm-otp', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'confirm_otp']);
Route::post('/seller/logout', [App\Http\Controllers\Api\seller\Seller_Auth::class, 'logout']);

Route::post('/seller/dashboard', [App\Http\Controllers\Api\seller\DashboardController::class, 'dashboard']);



//My products
Route::post('/seller/my-products-list', [App\Http\Controllers\Api\seller\MyProducts::class, 'list']);
Route::post('/seller/my-products-delete', [App\Http\Controllers\Api\seller\MyProducts::class, 'delete_product']);