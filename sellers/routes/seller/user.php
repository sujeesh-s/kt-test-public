<?php

use Illuminate\Support\Facades\Route; 

Route::get('/login', [App\Http\Controllers\Seller\Auth\LoginController::class, 'showSellerLoginForm'])->name('login');
Route::get('/seller/register', [App\Http\Controllers\Seller\Auth\RegisterController::class, 'showSellerRegisterForm']);
Route::get('/reset/password/{token}', [App\Http\Controllers\Seller\Auth\LoginController::class, 'resetPassword']);
Route::get('/seller/profile', [App\Http\Controllers\Seller\SellerController::class, 'profile']);

Route::get('/profile', [App\Http\Controllers\Seller\SellerController::class, 'seller'])->name('profile');
Route::post('/profile/validate', [App\Http\Controllers\Seller\SellerController::class, 'validateSeller']);
Route::post('/profile/save', [App\Http\Controllers\Seller\SellerController::class, 'saveSeller']);


Route::post('/validate/seller', [App\Http\Controllers\Seller\Auth\RegisterController::class, 'validateSeller']);
Route::post('/create/seller', [App\Http\Controllers\Seller\Auth\RegisterController::class, 'createSeller']);
Route::post('/seller/login', [App\Http\Controllers\Seller\Auth\LoginController::class, 'sellerLogin']);
Route::post('/forgot/password', [App\Http\Controllers\Seller\Auth\LoginController::class, 'forgotPassword']);
Route::post('/update/password', [App\Http\Controllers\Seller\Auth\LoginController::class, 'updatePassword']);
Route::get('/approval', [App\Http\Controllers\Seller\Auth\LoginController::class, 'showApproval'])->name('approval');
Route::get('/otp-verification', [App\Http\Controllers\Seller\Auth\LoginController::class, 'otpPage'])->name('otppage');
Route::post('/verify/otp', [App\Http\Controllers\Seller\Auth\LoginController::class, 'sellerOtp']);
Route::post('/create/password', [App\Http\Controllers\Seller\Auth\LoginController::class, 'createPassword']);


Route::get('/dashboard', [App\Http\Controllers\Seller\SellerController::class, 'dashboard']);

Route::get('/seller/logout', [App\Http\Controllers\Seller\SellerController::class, 'sellerLogout']);
Route::view('/seller/forgot-password', 'seller.auth.forgot_pwd');


Route::get('/my-products', [App\Http\Controllers\Seller\SellerProductController::class, 'products']);
Route::get('/my-product/{id}', [App\Http\Controllers\Seller\SellerProductController::class, 'product']);
Route::get('/my-product/{id}/{sellerId}', [App\Http\Controllers\Seller\SellerProductController::class, 'product']);
Route::get('/my-product/{id}/{sellerId}/{type}', [App\Http\Controllers\Seller\SellerProductController::class, 'product']);
Route::get('/admin-product/{id}', [App\Http\Controllers\Seller\SellerProductController::class, 'adminProduct']);

Route::get('/my-products/offer/{id}', [App\Http\Controllers\Seller\SellerProductController::class, 'specialOffer']);
Route::post('/my-products/offer/save', [App\Http\Controllers\Seller\SellerProductController::class, 'saveOffer']);


Route::get('/my-products/product-stocks', [App\Http\Controllers\Seller\SellerProductController::class, 'stocks']);
Route::post('/my-products/import', [App\Http\Controllers\Seller\SellerProductController::class, 'importProducts']);

Route::post('/my-products/product/validate', [App\Http\Controllers\Seller\SellerProductController::class, 'validateProduct']);
Route::post('/my-products/product/save', [App\Http\Controllers\Seller\SellerProductController::class, 'saveProduct']);
Route::post('/my-products/product/updateStatus', [App\Http\Controllers\Seller\SellerProductController::class, 'updateStatus']);

Route::post('/my-products/product-stock/add', [App\Http\Controllers\Seller\SellerProductController::class, 'stock']);
Route::post('/my-products/product-stock/save', [App\Http\Controllers\Seller\SellerProductController::class, 'saveStock']);


Route::get('/auctions', [App\Http\Controllers\Seller\AuctionController::class, 'auctions'])->name('seller.auctions');
Route::get('/auctions/log/{id}', [App\Http\Controllers\Seller\AuctionController::class, 'logAuction'])->name('auctions.log');
Route::post('/auctions/status', [App\Http\Controllers\Seller\AuctionController::class, 'auctionStatus']);
Route::post('/auctions/filter', [App\Http\Controllers\Seller\AuctionController::class, 'auctionFilter']);
Route::post('/auctions/delete', [App\Http\Controllers\Seller\AuctionController::class, 'auctionDelete']);
Route::get('/auctions/create', [App\Http\Controllers\Seller\AuctionController::class, 'auctionCreate'])->name('auctions.create');
Route::get('/auctions/edit/{id}', [App\Http\Controllers\Seller\AuctionController::class, 'editAuction'])->name('auctions.edit');
Route::get('/auctions/view/{id}', [App\Http\Controllers\Seller\AuctionController::class, 'viewAuction'])->name('auctions.view');
Route::post('/auctions/save', [App\Http\Controllers\Seller\AuctionController::class, 'auctionSave']);
Route::post('/auctions/subcategory', [App\Http\Controllers\Seller\AuctionController::class, 'subcatedata']);
Route::post('/auctions/prodsdata', [App\Http\Controllers\Seller\AuctionController::class, 'productdata']);
Route::get('/auctions/cron', [App\Http\Controllers\Seller\AuctionUpdateController::class, 'auctionsCron'])->name('auctions.cron');

Route::get('/coupons', [App\Http\Controllers\Seller\CouponController::class, 'coupons'])->name('seller.coupons');
Route::get('/coupons/create', [App\Http\Controllers\Seller\CouponController::class, 'createCoupon'])->name('seller.create');
Route::get('/coupons/edit/{id}', [App\Http\Controllers\Seller\CouponController::class, 'editCoupon'])->name('seller.edit');
Route::get('/coupons/view/{id}', [App\Http\Controllers\Seller\CouponController::class, 'viewCoupon'])->name('seller.view');
Route::get('/coupons/log/{id}', [App\Http\Controllers\Seller\CouponController::class, 'logCoupon'])->name('seller.log');
Route::post('/coupons/save', [App\Http\Controllers\Seller\CouponController::class, 'couponsave']);
Route::post('/coupons/delete', [App\Http\Controllers\Seller\CouponController::class, 'couponDelete']);
Route::post('/coupons/status', [App\Http\Controllers\Seller\CouponController::class, 'couponstatus']);
Route::post('/coupons/subcategory', [App\Http\Controllers\Seller\CouponController::class, 'subcatedata']);
Route::post('/coupons/filter', [App\Http\Controllers\Seller\CouponController::class, 'couponfilter']);


Route::get('/offers', [App\Http\Controllers\Seller\OfferController::class, 'offers'])->name('seller.offers');
Route::get('/offers/create', [App\Http\Controllers\Seller\OfferController::class, 'createOffer'])->name('offers.create');
Route::post('/offers/save', [App\Http\Controllers\Seller\OfferController::class, 'saveOffer']);
Route::post('/offers/status', [App\Http\Controllers\Seller\OfferController::class, 'offerStatus']);



Route::get('/customers', [App\Http\Controllers\Seller\CustomerController::class, 'index'])->name('admin.customer');
Route::get('/customer/view/{id}', [App\Http\Controllers\Seller\CustomerController::class, 'view_customer']);
Route::get('/customer/invoice/{id}', [App\Http\Controllers\Seller\CustomerController::class, 'invoice'])->name('customer.invoice');


Route::get('/shocking-sales', [App\Http\Controllers\Seller\ShockingSaleController::class, 'shocking_sales'])->name('seller.shocking_sales');
Route::get('/shocking-sales/create/{id}', [App\Http\Controllers\Seller\ShockingSaleController::class, 'createShockingSale'])->name('shocking_sales.create');
Route::post('/shocking-sales/save', [App\Http\Controllers\Seller\ShockingSaleController::class, 'saveShockingSale']);
Route::post('/shocking-sales/status', [App\Http\Controllers\Seller\ShockingSaleController::class, 'ShockingSaleStatus']);




Route::get('/stock-price', [App\Http\Controllers\Seller\StockPriceController::class, 'stock_price'])->name('seller.stock_price');
Route::get('/stock-price/log/{id}', [App\Http\Controllers\Seller\StockPriceController::class, 'stockPriceLog']);
Route::post('/stock-price/add', [App\Http\Controllers\Seller\StockPriceController::class, 'stock']);
Route::post('/stock-price/save', [App\Http\Controllers\Seller\StockPriceController::class, 'saveStock']);
Route::post('/stock-price/price/save', [App\Http\Controllers\Seller\StockPriceController::class, 'savePrice']);

//Visitors

Route::get('/report/product-visit/', [App\Http\Controllers\Seller\VisitReport::class, 'product_visit']);
Route::post('/report/product-visit/', [App\Http\Controllers\Seller\VisitReport::class, 'product_visit']);

//coupon report
Route::get('/report/coupon-report/', [App\Http\Controllers\Seller\ReportController::class, 'coupon_report']);
Route::post('/report/coupon-report/', [App\Http\Controllers\Seller\ReportController::class, 'coupon_report']);

//wishlist report
Route::get('/report/wishlist-report/', [App\Http\Controllers\Seller\ReportController::class, 'wishlist_report']);
Route::post('/report/wishlist-report/', [App\Http\Controllers\Seller\ReportController::class, 'wishlist_report']);

//bestpurchase report
Route::get('/report/best-purchase-report/', [App\Http\Controllers\Seller\ReportController::class, 'best_purchase_report']);
Route::post('/report/best-purchase-report/', [App\Http\Controllers\Seller\ReportController::class, 'best_purchase_report']);

//prdreview report
Route::get('/report/product-review-report/', [App\Http\Controllers\Seller\ReportController::class, 'review_report']);
Route::post('/report/product-review-report/', [App\Http\Controllers\Seller\ReportController::class, 'review_report']);
Route::get('/report/product-review-report/{id}/{type}', [App\Http\Controllers\Seller\ReportController::class, 'review_report']);

//seller side support
Route::get('/support/', [App\Http\Controllers\Seller\SupportSeller::class, 'list']);
Route::post('/support/', [App\Http\Controllers\Seller\SupportSeller::class, 'list']);
Route::get('/support/create/{id}/{type}', [App\Http\Controllers\Seller\SupportSeller::class, 'create']);
Route::post('/support/create/{id}/{type}', [App\Http\Controllers\Seller\SupportSeller::class, 'create']);
Route::get('/support/chat/{id}/{type}', [App\Http\Controllers\Seller\SupportSeller::class, 'chat']);

//chat seller
Route::get('/chat/list', [App\Http\Controllers\Seller\ChatsController::class, 'chat_list']);
Route::get('/chat/chat-message/{id}/{type}', [App\Http\Controllers\Seller\ChatsController::class, 'chat_messages']);
Route::get('/chat/chat-message-new/{id}/{type}', [App\Http\Controllers\Seller\ChatsController::class, 'open_chat_messages']);
Route::post('/chat/send-message/{id}/{type}', [App\Http\Controllers\Seller\ChatsController::class, 'chat_messages']);



Route::get('/modules', [App\Http\Controllers\Seller\ModulesController::class, 'modules'])->name('seller.modules');
Route::post('/modules/save', [App\Http\Controllers\Seller\ModulesController::class, 'moduleSave']);
Route::post('/modules/delete', [App\Http\Controllers\Seller\ModulesController::class, 'moduleDelete']);
Route::post('/modules/status', [App\Http\Controllers\Seller\ModulesController::class, 'moduleStatus']);
Route::post('/modules/sort-order', [App\Http\Controllers\Seller\ModulesController::class, 'sort_order'])->name('modules.sort-order');

Route::get('/user-roles', [App\Http\Controllers\Seller\UserRoleController::class, 'userRole'])->name('seller.user-roles');
Route::get('/user-roles/create', [App\Http\Controllers\Seller\UserRoleController::class, 'createRole'])->name('userRole.create');
Route::get('/user-roles/edit/{role}', [App\Http\Controllers\Seller\UserRoleController::class, 'editRole'])->name('userRole.edit');
Route::get('/user-roles/view/{role}', [App\Http\Controllers\Seller\UserRoleController::class, 'viewRole'])->name('userRole.view');
Route::post('/user-roles/save', [App\Http\Controllers\Seller\UserRoleController::class, 'roleSave']);
Route::post('/user-roles/delete', [App\Http\Controllers\Seller\UserRoleController::class, 'roleDelete']);
Route::post('/user-roles/status', [App\Http\Controllers\Seller\UserRoleController::class, 'roleStatus']);


Route::get('/staff-list', [App\Http\Controllers\Seller\StaffController::class, 'staffs'])->name('seller.staffs');
Route::get('/staff-list/create', [App\Http\Controllers\Seller\StaffController::class, 'createStaff'])->name('staffs.create');
Route::get('/staff-list/edit/{role}', [App\Http\Controllers\Seller\StaffController::class, 'editStaff'])->name('staffs.edit');
Route::get('/staff-list/view/{staff}', [App\Http\Controllers\Seller\StaffController::class, 'viewStaff'])->name('staffs.view');
Route::post('/staff-list/save', [App\Http\Controllers\Seller\StaffController::class, 'staffSave']);
Route::post('/staff-list/status', [App\Http\Controllers\Seller\StaffController::class, 'staffStatus']);
Route::post('/staff-list/delete', [App\Http\Controllers\Seller\StaffController::class, 'staffDelete']);


//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});


//Clear Route cache:
Route::get('/route-clear', function() {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function() {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

Route::get('/clear', function() {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    return "Cleared!";

});