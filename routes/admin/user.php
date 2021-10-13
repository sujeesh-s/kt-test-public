<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index']);
Route::get('/admin/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index']);

Route::get('/admin/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'showAdminLoginForm']);
Route::get('/admin/logout', [App\Http\Controllers\Admin\AdminController::class, 'adminLogout']);

 // Route::view('/admin', 'admin.admin');
Route::get('/admin/profile', [App\Http\Controllers\Admin\AdminController::class, 'profile']);
Route::post('admin/validate/profile', [App\Http\Controllers\Admin\AdminController::class, 'validateUser']);
Route::post('admin/profile/update', [App\Http\Controllers\Admin\AdminController::class, 'saveProfile']);
Route::post('admin/password/validate', [App\Http\Controllers\Admin\AdminController::class, 'validatePassword']);
Route::post('admin/change/password', [App\Http\Controllers\Admin\AdminController::class, 'savePassword']);

Route::post('/forgot/password', [App\Http\Controllers\Admin\Auth\LoginController::class, 'forgotPassword']);
Route::post('/update/password', [App\Http\Controllers\Admin\Auth\LoginController::class, 'updatePassword']);
Route::get('/reset/password/{token}', [App\Http\Controllers\Admin\Auth\LoginController::class, 'resetPassword']);


Route::post('/admin/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'adminLogin']);

Route::get('/admin/user-roles', [App\Http\Controllers\Admin\AdminController::class, 'userRole']);

Route::get('/admin/modules', [App\Http\Controllers\Admin\AdminController::class, 'modules']);

Route::view('/admin/seller', 'admin.seller.seller_registration');


Route::get('/admin/modules', [App\Http\Controllers\Admin\ModulesController::class, 'modules'])->name('admin.modules');
Route::post('/admin/modules/save', [App\Http\Controllers\Admin\ModulesController::class, 'moduleSave']);
Route::post('/admin/modules/delete', [App\Http\Controllers\Admin\ModulesController::class, 'moduleDelete']);
Route::post('/admin/modules/status', [App\Http\Controllers\Admin\ModulesController::class, 'moduleStatus']);

Route::get('/admin/user-roles', [App\Http\Controllers\Admin\UserRoleController::class, 'userRole'])->name('admin.user-roles');
Route::get('/admin/user-roles/create', [App\Http\Controllers\Admin\UserRoleController::class, 'createRole'])->name('userRole.create');
Route::get('/admin/user-roles/edit/{role}', [App\Http\Controllers\Admin\UserRoleController::class, 'editRole'])->name('userRole.edit');
Route::get('/admin/user-roles/view/{role}', [App\Http\Controllers\Admin\UserRoleController::class, 'viewRole'])->name('userRole.view');
Route::post('/admin/user-roles/save', [App\Http\Controllers\Admin\UserRoleController::class, 'roleSave']);
Route::post('/admin/user-roles/delete', [App\Http\Controllers\Admin\UserRoleController::class, 'roleDelete']);
Route::post('/admin/user-roles/status', [App\Http\Controllers\Admin\UserRoleController::class, 'roleStatus']);

Route::get('/admin/admins-list', [App\Http\Controllers\Admin\AdminController::class, 'admins'])->name('admin.admins');
Route::get('/admin/admins-list/create', [App\Http\Controllers\Admin\AdminController::class, 'createAdmin'])->name('admins.create');
Route::get('/admin/admins-list/edit/{role}', [App\Http\Controllers\Admin\AdminController::class, 'editAdmin'])->name('admins.edit');
Route::get('/admin/admins-list/view/{admin}', [App\Http\Controllers\Admin\AdminController::class, 'viewAdmin'])->name('admins.view');
Route::post('/admin/admins-list/save', [App\Http\Controllers\Admin\AdminController::class, 'adminSave']);
Route::post('/admin/admins-list/status', [App\Http\Controllers\Admin\AdminController::class, 'adminStatus']);
Route::post('/admin/admins-list/delete', [App\Http\Controllers\Admin\AdminController::class, 'adminDelete']);

Route::get('/admin/brands', [App\Http\Controllers\Admin\BrandController::class, 'brands'])->name('admin.brands');
Route::get('/admin/brands/create', [App\Http\Controllers\Admin\BrandController::class, 'createBrand'])->name('brands.create');
Route::get('/admin/brands/edit/{brand}', [App\Http\Controllers\Admin\BrandController::class, 'editBrand'])->name('brands.edit');
Route::get('/admin/brands/view/{brand}', [App\Http\Controllers\Admin\BrandController::class, 'viewBrand'])->name('brands.view');
Route::post('/admin/brands/save', [App\Http\Controllers\Admin\BrandController::class, 'brandSave']);
Route::post('/admin/brands/delete', [App\Http\Controllers\Admin\BrandController::class, 'brandDelete']);
Route::post('/admin/brands/status', [App\Http\Controllers\Admin\BrandController::class, 'brandStatus']);


Route::get('/admin/tags', [App\Http\Controllers\Admin\TagController::class, 'tags'])->name('admin.tags');
Route::get('/admin/tags/create', [App\Http\Controllers\Admin\TagController::class, 'createTag'])->name('tags.create');
Route::get('/admin/tags/edit/{tag}', [App\Http\Controllers\Admin\TagController::class, 'editTag'])->name('tags.edit');
Route::get('/admin/tags/view/{tag}', [App\Http\Controllers\Admin\TagController::class, 'viewTag'])->name('tags.view');
Route::post('/admin/tags/save', [App\Http\Controllers\Admin\TagController::class, 'tagSave']);
Route::post('/admin/tags/delete', [App\Http\Controllers\Admin\TagController::class, 'tagDelete']);
Route::post('/admin/tags/status', [App\Http\Controllers\Admin\TagController::class, 'tagStatus']);
Route::post('/admin/tags/subcategory', [App\Http\Controllers\Admin\TagController::class, 'subcatedata']);

Route::get('/admin/tax', [App\Http\Controllers\Admin\TaxController::class, 'tax'])->name('admin.tax');
Route::get('/admin/tax/create', [App\Http\Controllers\Admin\TaxController::class, 'createTax'])->name('tax.create');
Route::get('/admin/tax/edit/{tax}', [App\Http\Controllers\Admin\TaxController::class, 'editTax'])->name('tax.edit');
Route::get('/admin/tax/view/{tax}', [App\Http\Controllers\Admin\TaxController::class, 'viewTax'])->name('tax.view');
Route::post('/admin/tax/save', [App\Http\Controllers\Admin\TaxController::class, 'taxSave']);
Route::post('/admin/tax/delete', [App\Http\Controllers\Admin\TaxController::class, 'taxDelete']);
Route::post('/admin/tax/status', [App\Http\Controllers\Admin\TaxController::class, 'taxStatus']);
Route::post('/admin/tax/states', [App\Http\Controllers\Admin\TaxController::class, 'statesdata']);

Route::get('/admin/coupons', [App\Http\Controllers\Admin\CouponController::class, 'coupons'])->name('admin.coupons');
Route::get('/admin/coupons/create', [App\Http\Controllers\Admin\CouponController::class, 'createCoupon'])->name('coupons.create');
Route::get('/admin/coupons/edit/{id}', [App\Http\Controllers\Admin\CouponController::class, 'editCoupon'])->name('coupons.edit');
Route::get('/admin/coupons/view/{id}', [App\Http\Controllers\Admin\CouponController::class, 'viewCoupon'])->name('coupons.view');
Route::get('/admin/coupons/log/{id}', [App\Http\Controllers\Admin\CouponController::class, 'logCoupon'])->name('coupons.log');
Route::post('/admin/coupons/save', [App\Http\Controllers\Admin\CouponController::class, 'couponsave']);
Route::post('/admin/coupons/delete', [App\Http\Controllers\Admin\CouponController::class, 'couponDelete']);
Route::post('/admin/coupons/status', [App\Http\Controllers\Admin\CouponController::class, 'couponstatus']);
Route::post('/admin/coupons/subcategory', [App\Http\Controllers\Admin\CouponController::class, 'subcatedata']);
Route::post('/admin/coupons/filter', [App\Http\Controllers\Admin\CouponController::class, 'couponfilter']);

Route::get('/admin/discounts', [App\Http\Controllers\Admin\OfferController::class, 'offers'])->name('admin.offers');
Route::get('/admin/discounts/create', [App\Http\Controllers\Admin\OfferController::class, 'createOffer'])->name('offers.create');
Route::post('/admin/discounts/save', [App\Http\Controllers\Admin\OfferController::class, 'saveOffer']);
Route::post('/admin/discounts/status', [App\Http\Controllers\Admin\OfferController::class, 'offerStatus']);
Route::post('/admin/discounts/products', [App\Http\Controllers\Admin\OfferController::class, 'sellerProducts']);


Route::get('/admin/rewards', [App\Http\Controllers\Admin\RewardController::class, 'rewards'])->name('admin.rewards');
Route::post('/admin/rewards/save', [App\Http\Controllers\Admin\RewardController::class, 'rewardSave']);

Route::get('/admin/auctions', [App\Http\Controllers\Admin\AuctionController::class, 'auctions'])->name('admin.auctions');
Route::get('/admin/auctions/log/{id}', [App\Http\Controllers\Admin\AuctionController::class, 'logAuction'])->name('auctions.log');
Route::post('/admin/auctions/status', [App\Http\Controllers\Admin\AuctionController::class, 'auctionStatus']);
Route::post('/admin/auctions/filter', [App\Http\Controllers\Admin\AuctionController::class, 'auctionFilter']);

Route::get('/admin/auction/refund-request', [App\Http\Controllers\Admin\AuctionController::class, 'auctionRefundRequests']);
Route::get('/admin/auction/refund-log/{id}', [App\Http\Controllers\Admin\AuctionController::class, 'logRefundRequests'])->name('refund.log');
Route::post('/admin/auction/process-refund', [App\Http\Controllers\Admin\AuctionController::class, 'processRefund']);

Route::get('/admin/category', [App\Http\Controllers\Admin\CategoryController::class, 'category'])->name('admin.category');
Route::get('/admin/category-new', [App\Http\Controllers\Admin\CategoryController::class, 'insert_category'])->name('admin.newcategory');
Route::post('/admin/insert-category', [App\Http\Controllers\Admin\CategoryController::class, 'create_category']);
Route::get('/admin/category/edit/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'edit_category']);
Route::post('/admin/update-category/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'update_category'])->name('category.update');
Route::post('/admin/delete-category/', [App\Http\Controllers\Admin\CategoryController::class, 'delete_category']);
Route::post('/admin/category/change-status/', [App\Http\Controllers\Admin\CategoryController::class, 'change_status']);
Route::post('/admin/category/sort-order', [App\Http\Controllers\Admin\CategoryController::class, 'sort_order'])->name('category.sort-order');
Route::get('/admin/category/view/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'view_category']);

Route::get('/admin/subcategory-new/', [App\Http\Controllers\Admin\CategoryController::class, 'new_subcategory'])->name('subcategory.new');
Route::get('/admin/subcategory/list/{cid}/{sid}', [App\Http\Controllers\Admin\CategoryController::class, 'subcatedata']);
Route::post('/admin/insert-subcategory', [App\Http\Controllers\Admin\CategoryController::class, 'create_subcategory']);
Route::get('/admin/subcategory', [App\Http\Controllers\Admin\CategoryController::class, 'subcategory'])->name('admin.subcategory');
Route::post('/admin/delete-subcategory/', [App\Http\Controllers\Admin\CategoryController::class, 'delete_subcategory']);
Route::post('/admin/category/change-status-subcategory/', [App\Http\Controllers\Admin\CategoryController::class, 'change_status_subcategory']);
Route::post('/admin/update-subcategory/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'update_subcategory'])->name('subcategory.update');
Route::get('/admin/subcategory/edit/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'edit_subcategory']);
Route::get('/admin/subcategory/view/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'view_subcategory']);

Route::get('/admin/product/list', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.productlist');
Route::post('/admin/product/list', [App\Http\Controllers\Admin\ProductController::class, 'index']);
Route::get('/admin/product/', [App\Http\Controllers\Admin\ProductController::class, 'product'])->name('admin.product');
Route::post('/admin/subcategory-ajax/', [App\Http\Controllers\Admin\ProductController::class, 'subCat'])->name('subcategory_ajax');
Route::post('/admin/taglist-ajax/', [App\Http\Controllers\Admin\ProductController::class, 'taglist'])->name('taglist_ajax');
Route::post('/admin/insert-product/', [App\Http\Controllers\Admin\ProductController::class, 'insert_product'])->name('admin.insert-product');
Route::post('/admin/product/change-status-product/', [App\Http\Controllers\Admin\ProductController::class, 'change_status_product']);
Route::post('/admin/product/delete-product/', [App\Http\Controllers\Admin\ProductController::class, 'delete_product']);
Route::get('/admin/product/edit/{id}', [App\Http\Controllers\Admin\ProductController::class, 'edit_product']);
Route::post('/admin/update-product/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update_product']);
Route::post('/admin/product/remove-image', [App\Http\Controllers\Admin\ProductController::class, 'remove_image']);

//visitor report
Route::get('/admin/report/product-visit/', [App\Http\Controllers\Admin\VisitReport::class, 'product_visit']);
Route::post('/admin/report/product-visit/', [App\Http\Controllers\Admin\VisitReport::class, 'product_visit']);

//coupon report
Route::get('/admin/report/coupon-report/', [App\Http\Controllers\Admin\ReportController::class, 'coupon_report']);
Route::post('/admin/report/coupon-report/', [App\Http\Controllers\Admin\ReportController::class, 'coupon_report']);

//wishlist report
Route::get('/admin/report/wishlist-report/', [App\Http\Controllers\Admin\ReportController::class, 'wishlist_report']);
Route::post('/admin/report/wishlist-report/', [App\Http\Controllers\Admin\ReportController::class, 'wishlist_report']);

//prdreview report
Route::get('/admin/report/product-review-report/', [App\Http\Controllers\Admin\ReportController::class, 'review_report']);
Route::post('/admin/report/product-review-report/', [App\Http\Controllers\Admin\ReportController::class, 'review_report']);
Route::get('/admin/report/product-review-report/{id}/{type}', [App\Http\Controllers\Admin\ReportController::class, 'review_report']);

//bestpurchase report
Route::get('/admin/report/best-purchase-report/', [App\Http\Controllers\Admin\ReportController::class, 'best_purchase_report']);
Route::post('/admin/report/best-purchase-report/', [App\Http\Controllers\Admin\ReportController::class, 'best_purchase_report']);

//chat Admin side
Route::get('/admin/chat/list', [App\Http\Controllers\Admin\ChatsController::class, 'list']);
Route::get('/admin/chat/chat-message/{id}/{type}', [App\Http\Controllers\Admin\ChatsController::class, 'chat']);


Route::get('/admin/shocking-sales', [App\Http\Controllers\Admin\ShockingSaleController::class, 'shocking_sales'])->name('admin.shocking_sales');
Route::get('/admin/shocking-sales/create', [App\Http\Controllers\Admin\ShockingSaleController::class, 'createShockingSale'])->name('shocking_sales.create');
Route::post('/admin/shocking-sales/save', [App\Http\Controllers\Admin\ShockingSaleController::class, 'saveShockingSale']);
Route::post('/admin/shocking-sales/status', [App\Http\Controllers\Admin\ShockingSaleController::class, 'ShockingSaleStatus']);
Route::post('/admin/shocking-sales/delete', [App\Http\Controllers\Admin\ShockingSaleController::class, 'ShockingSaleDelete']);
Route::get('/admin/shocking-sales/edit/{id}', [App\Http\Controllers\Admin\ShockingSaleController::class, 'editShockingSale'])->name('shocking.edit');


Route::get('/admin/settings', [App\Http\Controllers\Admin\SettingsOthersController::class, 'settings'])->name('admin.settings');
Route::post('/admin/settings/save', [App\Http\Controllers\Admin\SettingsOthersController::class, 'settingsSave']);

Route::post('/admin/editor-image', [App\Http\Controllers\Admin\SellerProductController::class, 'editorImage']);


Route::post('/admin/modules/sort-order', [App\Http\Controllers\Admin\ModulesController::class, 'sort_order'])->name('modules.sort-order');

Route::get('/admin/banners', [App\Http\Controllers\Admin\BannerController::class, 'banners'])->name('admin.banners');
Route::get('/admin/banners/create', [App\Http\Controllers\Admin\BannerController::class, 'createBanner'])->name('shocking_sales.create');
Route::post('/admin/banners/save', [App\Http\Controllers\Admin\BannerController::class, 'saveBanner']);
Route::post('/admin/banners/status', [App\Http\Controllers\Admin\BannerController::class, 'BannerStatus']);
Route::post('/admin/banners/delete', [App\Http\Controllers\Admin\BannerController::class, 'BannerDelete']);
Route::get('/admin/banners/edit/{id}', [App\Http\Controllers\Admin\BannerController::class, 'editBanner'])->name('banners.edit');

//support customer
Route::get('/admin/customer/support/', [App\Http\Controllers\Admin\SupportCustomer::class, 'list']);
Route::post('/admin/customer/support/', [App\Http\Controllers\Admin\SupportCustomer::class, 'list']);
Route::get('/admin/customer/support/create/{id}/{type}', [App\Http\Controllers\Admin\SupportCustomer::class, 'create']);
Route::post('/admin/customer/support/create/{id}/{type}', [App\Http\Controllers\Admin\SupportCustomer::class, 'create']);
Route::get('/admin/customer/support/chat/{id}/{type}', [App\Http\Controllers\Admin\SupportCustomer::class, 'chat']);


//seller support
Route::get('/admin/support/', [App\Http\Controllers\Admin\SupportSeller::class, 'list']);
Route::post('/admin/support/', [App\Http\Controllers\Admin\SupportSeller::class, 'list']);
Route::get('/admin/support/create/{id}/{type}', [App\Http\Controllers\Admin\SupportSeller::class, 'create']);
Route::post('/admin/support/create/{id}/{type}', [App\Http\Controllers\Admin\SupportSeller::class, 'create']);
Route::get('/admin/support/chat/{id}/{type}', [App\Http\Controllers\Admin\SupportSeller::class, 'chat']);


// dashboard chart
Route::post('/admin/visitlog', [App\Http\Controllers\Admin\AdminController::class, 'visitlog']);
Route::post('/admin/salelog', [App\Http\Controllers\Admin\AdminController::class, 'salelog']);

//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

//Reoptimized class loader:
Route::get('/optimize', function() {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});

//Route cache:
Route::get('/route-cache', function() {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
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