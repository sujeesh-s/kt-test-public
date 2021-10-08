<?php

use Illuminate\Support\Facades\Route;

//Country
Route::post('/customer/country', [App\Http\Controllers\Api\Customer\OrderController::class, 'get_country']);
Route::post('/customer/state', [App\Http\Controllers\Api\Customer\OrderController::class, 'get_state']);
Route::post('/customer/city', [App\Http\Controllers\Api\Customer\OrderController::class, 'get_city']);

Route::post('/customer/register', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'register']);
Route::post('/customer/login', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'login']);
Route::post('/customer/social/login', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'socialLogin']);
Route::post('/customer/verify/otp', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'verifyOtp']);

Route::post('/customer/home', [App\Http\Controllers\Api\Customer\Homepage::class, 'index']);
Route::post('/customer/product-category', [App\Http\Controllers\Api\Customer\ProductController::class, 'product_by_category']);
Route::post('/customer/product-subcategory', [App\Http\Controllers\Api\Customer\ProductController::class, 'product_by_subcategory']);
Route::post('/customer/product-brand', [App\Http\Controllers\Api\Customer\ProductController::class, 'product_by_brand']);
Route::post('/customer/product-detail', [App\Http\Controllers\Api\Customer\ProductController::class, 'product_detail']);
Route::post('/customer/product-dailydeal', [App\Http\Controllers\Api\Customer\ProductController::class, 'daily_deal']);
Route::post('/customer/shock-sale', [App\Http\Controllers\Api\Customer\ProductController::class, 'shocking_sale']);
Route::post('/customer/auction', [App\Http\Controllers\Api\Customer\ProductController::class, 'auction_product']);
Route::post('/customer/shop-detail', [App\Http\Controllers\Api\Customer\ProductController::class, 'shop_detail']);
Route::post('/customer/shop-product-search', [App\Http\Controllers\Api\Customer\ProductController::class, 'shop_product_search']);
Route::post('/customer/filter', [App\Http\Controllers\Api\Customer\Homepage::class, 'filter_on_search']);
Route::post('/customer/product-search', [App\Http\Controllers\Api\Customer\Homepage::class, 'product_search']);
Route::post('/customer/cat-subcat', [App\Http\Controllers\Api\Customer\Homepage::class, 'category_subcategory']);
Route::post('/customer/brand', [App\Http\Controllers\Api\Customer\Homepage::class, 'brands']);
Route::post('/customer/product-list', [App\Http\Controllers\Api\Customer\ProductController::class, 'product_list']);
Route::post('/customer/shock-sale-products', [App\Http\Controllers\Api\Customer\Homepage::class, 'shocking_sale_products']);
Route::post('/customer/product-list-filter', [App\Http\Controllers\Api\Customer\ProductController::class, 'product_list_filter']);
Route::post('/customer/product-featured', [App\Http\Controllers\Api\Customer\ProductController::class, 'featured_products']);
Route::post('/customer/product-deals', [App\Http\Controllers\Api\Customer\ProductController::class, 'daily_deals']);


Route::post('/customer/register/send/otp', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'regSendotp']);
Route::post('/customer/register/verify/otp', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'regVerifyotp']);
Route::post('/customer/login/send/otp', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'loginSendotp']);
Route::post('/customer/login/verify/otp', [App\Http\Controllers\Api\Customer\CustomerAuth_Api::class, 'loginVerifyotp']);

//CART
Route::post('/customer/cart', [App\Http\Controllers\Api\Customer\CartController::class, 'index']);
Route::post('/customer/delete-cart', [App\Http\Controllers\Api\Customer\CartController::class, 'delete_cart']);
Route::post('/customer/cart/total', [App\Http\Controllers\Api\Customer\CartController::class, 'cart_total']);


//Coupon
Route::post('/customer/cart/apply-coupon', [App\Http\Controllers\Api\Customer\CartController::class, 'apply_coupon']);


//order
Route::post('/customer/order/placeorder', [App\Http\Controllers\Api\Customer\OrderController::class, 'placeorder']);
Route::post('/customer/order/checkout-info', [App\Http\Controllers\Api\Customer\OrderController::class, 'checkout_info_page']);
Route::post('/customer/order/track-order', [App\Http\Controllers\Api\Customer\OrderController::class, 'track_order']);

//Support chat
Route::post('/customer/create-ticket', [App\Http\Controllers\Api\Customer\SupportCustomer::class, 'create_ticket']);
Route::post('/customer/list-ticket', [App\Http\Controllers\Api\Customer\SupportCustomer::class, 'ticket_list']);
Route::post('/customer/add-ticket-message', [App\Http\Controllers\Api\Customer\SupportCustomer::class, 'add_message']);
Route::post('/customer/support-message', [App\Http\Controllers\Api\Customer\SupportCustomer::class, 'view_message']);

//Seller customer CHAT
Route::post('/customer/chat/send', [App\Http\Controllers\Api\Customer\ChatsController::class, 'send_message']);
Route::post('/customer/chat/list', [App\Http\Controllers\Api\Customer\ChatsController::class, 'list']);
Route::post('/customer/chat/message', [App\Http\Controllers\Api\Customer\ChatsController::class, 'chat_message']);


//voucher
Route::post('/customer/coupon/seller', [App\Http\Controllers\Api\Customer\VoucherController::class, 'seller_voucher']);
Route::post('/customer/coupon/platform', [App\Http\Controllers\Api\Customer\VoucherController::class, 'admin_voucher']);





/*****************INSERT RESPONSES**************************/
Route::post('/customer/post-seller-review', [App\Http\Controllers\Api\Customer\InsertResponse::class, 'insert_seller_review']);
Route::post('/customer/create-bid', [App\Http\Controllers\Api\Customer\InsertResponse::class, 'add_bid']);
Route::post('/customer/post-product-review', [App\Http\Controllers\Api\Customer\InsertResponse::class, 'insert_product_review']);
Route::post('/customer/add-cart', [App\Http\Controllers\Api\Customer\InsertResponse::class, 'insert_cart']);
Route::post('/customer/cart/change-qty', [App\Http\Controllers\Api\Customer\InsertResponse::class, 'change_cart_qty']);
Route::post('/customer/add-wishlist', [App\Http\Controllers\Api\Customer\InsertResponse::class, 'insert_wishlist']);



/*****************MY ACCOUNT**************************/
Route::post('/customer/add/wishlist', [App\Http\Controllers\Api\Customer\AccountController::class, 'addWishlist']);
Route::post('/customer/wishlist', [App\Http\Controllers\Api\Customer\AccountController::class, 'wishlist']);
Route::post('/customer/remove/wishlist', [App\Http\Controllers\Api\Customer\AccountController::class, 'removeWishlist']);
Route::post('/customer/mypurchase', [App\Http\Controllers\Api\Customer\AccountController::class, 'purchase']);
Route::post('/customer/order/detail', [App\Http\Controllers\Api\Customer\AccountController::class, 'order_detail']);
Route::post('/customer/cancel/request', [App\Http\Controllers\Api\Customer\AccountController::class, 'cancel_request']);
Route::post('/customer/cancel/request/list/seller', [App\Http\Controllers\Api\Customer\AccountController::class, 'seller_req_list']);
Route::post('/customer/cancel/request/list/customer', [App\Http\Controllers\Api\Customer\AccountController::class, 'cust_req_list']);
Route::post('/customer/past/cancel/list/seller', [App\Http\Controllers\Api\Customer\AccountController::class, 'seller_past_list']);
Route::post('/customer/past/cancel/list/customer', [App\Http\Controllers\Api\Customer\AccountController::class, 'cust_past_list']);
Route::post('/customer/response/cancel/request', [App\Http\Controllers\Api\Customer\AccountController::class, 'response_request']);
Route::post('/customer/profile', [App\Http\Controllers\Api\Customer\AccountController::class, 'get_profile']);
Route::post('/customer/edit/profile', [App\Http\Controllers\Api\Customer\AccountController::class, 'edit_profile']);
Route::post('/customer/address', [App\Http\Controllers\Api\Customer\AccountController::class, 'userAddress']);
Route::post('/customer/add/address', [App\Http\Controllers\Api\Customer\AccountController::class, 'addAddress']);
Route::post('/customer/edit/address', [App\Http\Controllers\Api\Customer\AccountController::class, 'editAddress']);
Route::post('/customer/remove/address', [App\Http\Controllers\Api\Customer\AccountController::class, 'deleteAddress']);
Route::post('/customer/default/address', [App\Http\Controllers\Api\Customer\AccountController::class, 'defaultAddress']);
Route::post('/customer/logout', [App\Http\Controllers\Api\Customer\AccountController::class, 'logout']);
Route::post('/customer/return/request', [App\Http\Controllers\Api\Customer\AccountController::class, 'return_request']);
Route::post('/customer/usage/coupon', [App\Http\Controllers\Api\Customer\AccountController::class, 'usageCoupon']);
Route::post('/customer/recent/views', [App\Http\Controllers\Api\Customer\AccountController::class, 'recent_views']);
Route::post('/customer/wallet/amount', [App\Http\Controllers\Api\Customer\AccountController::class, 'wallet_amount']);
Route::post('/customer/view/notifications', [App\Http\Controllers\Api\Customer\AccountController::class, 'notifications']);
Route::post('/customer/order/invoice', [App\Http\Controllers\Api\Customer\AccountController::class, 'invoice']);
Route::post('/customer/order/return/shipment', [App\Http\Controllers\Api\Customer\AccountController::class, 'return_shipment']);

/*****************AUCTION**************************/
Route::post('/customer/auction/detail', [App\Http\Controllers\Api\Customer\AuctionController::class, 'auction_detail']);
Route::post('/customer/auction/checkout', [App\Http\Controllers\Api\Customer\AuctionController::class, 'auction_checkout']);
Route::post('/customer/auction/order/list', [App\Http\Controllers\Api\Customer\AuctionController::class, 'auction_order_list']);