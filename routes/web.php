<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Offline\Register\RegisterController; 
use App\Http\Controllers\Offline\Login\LoginController;
use App\Http\Controllers\Common\CommonController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Offline\Vendor\VendorController;
use App\Http\Controllers\Offline\Store\StoreMasterController;
use App\Http\Controllers\Offline\OnlineShop\OnlineShopController;
use App\Http\Controllers\Offline\Category\CategoryController;
use App\Http\Controllers\Offline\Product\ProductController;
use App\Http\Controllers\Offline\Unit\UnitController;
use App\Http\Controllers\Offline\Unit\Unit_ConvertController;
use App\Http\Controllers\Offline\Price\PriceController;
use App\Http\Controllers\Offline\Purchased\PurchasedController;
use App\Http\Controllers\Offline\DashboardController;
use App\Http\Controllers\Offline\StoreStock\StoreStockController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (NO LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/

// Registration
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/register/success', [RegisterController::class, 'success'])->name('register.success');

// Login
Route::get('/login', [LoginController::class, 'show'])->name('login');

// Forgot
Route::get('/forgot-password', [LoginController::class, 'showForgotPassword'])->name('forgot.password');
Route::get('/forgot-username', [LoginController::class, 'showForgotUsername'])->name('forgot.username');

// Public APIs
Route::post('/api/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/api/auth/recover-username', [AuthController::class, 'recoverUsername']);
Route::post('/api/auth/verify-pwd-identity', [AuthController::class, 'verifyPasswordIdentity']);
Route::post('/api/auth/verify-usr-identity', [AuthController::class, 'verifyUsernameIdentity']);
Route::post('/api/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/api/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('/registration-status', [LoginController::class, 'showRegistrationStatus'])->name('registration.status');

Route::post('/api/auth/force-logout-device', [AuthController::class, 'forceLogoutDevice']);
Route::post('/api/auth/verify-status-identity', [AuthController::class, 'verifyStatusIdentity']);
Route::post('/api/auth/get-registration-status', [AuthController::class, 'getRegistrationStatus']);

// Common APIs
Route::post('/api/check-username', [RegisterController::class, 'checkUsername']);
Route::get('/api/get-districts/{state_id}', [CommonController::class, 'getDistricts']);
Route::get('/api/get-blocks/{district_id}', [CommonController::class, 'getBlocks']);
Route::get('/api/get-municipalities/{district_id}', [CommonController::class, 'getMunicipalities']);
Route::get('/api/get-gram-panchayats/{block_id}', [CommonController::class, 'getGramPanchayats']);
Route::get('/api/get-villages/{gp_id}', [CommonController::class, 'getVillages']);
Route::get('/api/get-wards/{municipality_id}', [CommonController::class, 'getWards']);
Route::get('/api/get-post-offices-by-village/{vill_id}', [CommonController::class, 'getPostOfficesByVillage']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED WEB ROUTES (LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // DASHBOARD

    Route::get('/offline/dashboard/superadmin', function() { return view('Offline.Dashboard.superadmin'); });
    Route::get('/offline/dashboard/admin', function() { return view('Offline.Dashboard.admin'); });
    Route::get('/offline/dashboard/management', function() { return view('Offline.Dashboard.management'); });
    Route::get('/offline/dashboard/sales', function() { return view('Offline.Dashboard.sales'); });
    Route::get('/offline/dashboard/account', function() { return view('Offline.Dashboard.account'); });
    Route::get('/offline/dashboard/reporter', function() { return view('Offline.Dashboard.reporter'); });

    // Route::get('/offline/dashboard/superadmin', [DashboardController::class, 'superadmin']);
    // Route::get('/offline/dashboard/admin', [DashboardController::class, 'admin']);
    // Route::get('/offline/dashboard/management', [DashboardController::class, 'management']);
    // Route::get('/offline/dashboard/sales', [DashboardController::class, 'salesManager']);
    // Route::get('/offline/dashboard/account', [DashboardController::class, 'account']);
    // Route::get('/offline/dashboard/reporter', [DashboardController::class, 'reporter']);

    // MASTER PAGES
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendor.index');
    Route::get('/stores', [StoreMasterController::class, 'index'])->name('store.index');
    Route::get('/online-shops', [OnlineShopController::class, 'index'])->name('onlineshop.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
    Route::get('/products', [ProductController::class, 'index'])->name('product.index');
    Route::get('/units', [UnitController::class, 'index'])->name('unit.index');
    Route::get('/unit-conversions', [Unit_ConvertController::class, 'index'])->name('unit_convert.index');
    Route::get('/prices', [PriceController::class, 'index'])->name('price.index');

    // PURCHASE
    Route::get('/purchases', [PurchasedController::class, 'index'])->name('purchased.index');
    Route::get('/purchase-history', [PurchasedController::class, 'history'])->name('purchased.history');
    Route::get('/godown-stock/history/{enc_product_id}', [PurchasedController::class, 'productHistory'])->name('godown_stock.history');
    Route::get('/godown-stock', [PurchasedController::class, 'stock'])->name('purchased.stock');
    Route::get('/transaction-ledger', [PurchasedController::class, 'ledger'])->name('purchased.ledger');

    // Store Stock
    // Route::prefix('store-stock')->group(function () {
        Route::get('/store-transfers', [StoreStockController::class, 'index'])->name('store_stock.index');
        Route::get('/store-total-stock/{store_id?}', [StoreStockController::class, 'totalStock'])->name('store_stock.total');
        Route::get('/store-total-stock/history/{enc_store_id}/{enc_product_id}', [StoreStockController::class, 'productHistory'])->name('store_stock.history');    
        Route::get('/store-purchase-history/{enc_store_id?}', [StoreStockController::class, 'StorePurchaseHistory'])->name('store_purchase_history.inward');
        Route::get('/store/purchase-history/print/{id}', [StoreStockController::class, 'printChallan'])->name('purchase.print');
        Route::get('/store/print-barcodes', [StoreStockController::class, 'printBarcodes'])->name('store_stock.print_barcodes');
        Route::get('/store-all-transaction', [StoreStockController::class, 'StoreAllTransaction'])->name('store_all_stock.transaction');
    // });
    });
/*
|--------------------------------------------------------------------------
| API AUTH (JWT)
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'api/auth'], function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| PROTECTED API (ROLE BASED)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->middleware(['jwt.role:1,2'])->group(function () {

    // Vendor
    Route::post('/vendors', [VendorController::class, 'store']);
    Route::put('/vendors/{encrypted_id}', [VendorController::class, 'update']);
    Route::delete('/vendors/{encrypted_id}', [VendorController::class, 'destroy']);

    // Store
    Route::post('/stores', [StoreMasterController::class, 'store']);
    Route::put('/stores/{encrypted_id}', [StoreMasterController::class, 'update']);
    Route::delete('/stores/{encrypted_id}', [StoreMasterController::class, 'destroy']);

    // Online Shop
    Route::post('/online-shops', [OnlineShopController::class, 'store']);
    Route::put('/online-shops/{encrypted_id}', [OnlineShopController::class, 'update']);
    Route::delete('/online-shops/{encrypted_id}', [OnlineShopController::class, 'destroy']);

    // Category
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{encrypted_id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{encrypted_id}', [CategoryController::class, 'destroy']);

    // Product
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{encrypted_id}', [ProductController::class, 'update']);
    Route::delete('/products/{encrypted_id}', [ProductController::class, 'destroy']);

    // Unit
    Route::post('/units', [UnitController::class, 'store']);
    Route::put('/units/{encrypted_id}', [UnitController::class, 'update']);
    Route::delete('/units/{encrypted_id}', [UnitController::class, 'destroy']);

    // Unit Convert
    Route::post('/unit-conversions', [Unit_ConvertController::class, 'store']);
    Route::put('/unit-conversions/{encrypted_id}', [Unit_ConvertController::class, 'update']);
    Route::delete('/unit-conversions/{encrypted_id}', [Unit_ConvertController::class, 'destroy']);

    // Price
    Route::post('/prices', [PriceController::class, 'store']);
    Route::put('/prices/{encrypted_id}', [PriceController::class, 'update']);
    Route::delete('/prices/{encrypted_id}', [PriceController::class, 'destroy']);

    // Purchase
    Route::post('/purchases', [PurchasedController::class, 'store']);

    // Store Stock
    Route::post('/store-transfers/bulk', [StoreStockController::class, 'store']);


});



// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Offline\Register\RegisterController; 
// use App\Http\Controllers\Offline\Login\LoginController;
// use App\Http\Controllers\Common\CommonController;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Offline\Vendor\VendorController;
// use App\Http\Controllers\Offline\Store\StoreMasterController;
// use App\Http\Controllers\Offline\OnlineShop\OnlineShopController;
// use App\Http\Controllers\Offline\Category\CategoryController;
// use App\Http\Controllers\Offline\Product\ProductController;
// use App\Http\Controllers\Offline\Unit\UnitController;
// use App\Http\Controllers\Offline\Unit\Unit_ConvertController;
// use App\Http\Controllers\Offline\Price\PriceController;
// use App\Http\Controllers\Offline\Purchased\PurchasedController;


// // --- REGISTRATION ROUTES ---
// Route::get('/register', [RegisterController::class, 'show'])->name('register');
// Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
// Route::get('/register/success', [RegisterController::class, 'success'])->name('register.success');

// // --- WEB LOGIN ROUTE ---
// Route::get('/login', [LoginController::class, 'show'])->name('login');


// // ... under your login route
// Route::get('/forgot-password', [LoginController::class, 'showForgotPassword'])->name('forgot.password');
// Route::get('/forgot-username', [LoginController::class, 'showForgotUsername'])->name('forgot.username');
// Route::post('/api/auth/reset-password', [AuthController::class, 'resetPassword']);
// Route::post('/api/auth/recover-username', [AuthController::class, 'recoverUsername']);
// Route::post('/api/auth/verify-pwd-identity', [AuthController::class, 'verifyPasswordIdentity']);
// Route::post('/api/auth/verify-usr-identity', [AuthController::class, 'verifyUsernameIdentity']);
// Route::post('/api/auth/send-otp', [AuthController::class, 'sendOtp']);
// Route::post('/api/auth/verify-otp', [AuthController::class, 'verifyOtp']);
// Route::get('/registration-status', [LoginController::class, 'showRegistrationStatus'])->name('registration.status');

// Route::post('/api/auth/force-logout-device', [AuthController::class, 'forceLogoutDevice']);
// Route::post('/api/auth/verify-status-identity', [AuthController::class, 'verifyStatusIdentity']);
// Route::post('/api/auth/get-registration-status', [AuthController::class, 'getRegistrationStatus']);


// // Route::get('/offline/dashboard/superadmin', [DashboardController::class, 'superadmin']);
// // Route::get('/offline/dashboard/admin', [DashboardController::class, 'admin']);
// // Route::get('/offline/dashboard/sales', [DashboardController::class, 'salesManager']);


// // --- COMMON API ROUTES ---
// Route::post('/api/check-username', [RegisterController::class, 'checkUsername']);
// Route::get('/api/get-districts/{state_id}', [CommonController::class, 'getDistricts']);
// Route::get('/api/get-blocks/{district_id}', [CommonController::class, 'getBlocks']);
// Route::get('/api/get-municipalities/{district_id}', [CommonController::class, 'getMunicipalities']);
// Route::get('/api/get-gram-panchayats/{block_id}', [CommonController::class, 'getGramPanchayats']);
// Route::get('/api/get-villages/{gp_id}', [CommonController::class, 'getVillages']);
// Route::get('/api/get-wards/{municipality_id}', [CommonController::class, 'getWards']);
// Route::get('/api/get-post-offices-by-village/{vill_id}', [CommonController::class, 'getPostOfficesByVillage']);

// // --- JWT AUTH API ROUTES ---
// Route::group(['prefix' => 'api/auth'], function () {
//     Route::post('login', [AuthController::class, 'login']);
    
//     Route::group(['middleware' => 'auth:api'], function () {
//         Route::post('logout', [AuthController::class, 'logout']);
//         Route::post('refresh', [AuthController::class, 'refresh']);
//         Route::post('me', [AuthController::class, 'me']);
//     });
// });

// // --- DASHBOARD ROUTES (Examples) ---
// Route::get('/offline/dashboard/superadmin', function() { return view('Offline.Dashboard.superadmin'); });
// Route::get('/offline/dashboard/admin', function() { return view('Offline.Dashboard.admin'); });
// Route::get('/offline/dashboard/management', function() { return view('Offline.Dashboard.management'); });
// Route::get('/offline/dashboard/sales', function() { return view('Offline.Dashboard.sales'); });
// Route::get('/offline/dashboard/account', function() { return view('Offline.Dashboard.account'); });
// Route::get('/offline/dashboard/reporter', function() { return view('Offline.Dashboard.reporter'); });


// Route::get('/vendors', [VendorController::class, 'index'])->name('vendor.index');
// Route::get('/stores', [StoreMasterController::class, 'index'])->name('store.index');
// Route::get('/online-shops', [OnlineShopController::class, 'index'])->name('onlineshop.index');
// Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
// Route::get('/products', [ProductController::class, 'index'])->name('product.index');
// Route::get('/units', [UnitController::class, 'index'])->name('unit.index');
// Route::get('/unit-conversions', [Unit_ConvertController::class, 'index'])->name('unit_convert.index');
// Route::get('/prices', [PriceController::class, 'index'])->name('price.index');
// Route::get('/purchases', [PurchasedController::class, 'index'])->name('purchased.index');
// Route::get('/purchase-history', [PurchasedController::class, 'history'])->name('purchased.history');
// Route::get('/godown-stock', [PurchasedController::class, 'stock'])->name('purchased.stock');
// Route::get('/transaction-ledger', [PurchasedController::class, 'ledger'])->name('purchased.ledger');


// // Protect this route for Super Admin (0) and Admin (1)
// Route::prefix('api')->middleware(['jwt.role:1,2'])->group(function () {

//     // Vendor Master API
//     Route::post('/vendors', [VendorController::class, 'store']);
//     Route::put('/vendors/{encrypted_id}', [VendorController::class, 'update']);
//     Route::delete('/vendors/{encrypted_id}', [VendorController::class, 'destroy']);

//     // Store Master API
//     Route::post('/stores', [StoreMasterController::class, 'store']);
//     Route::put('/stores/{encrypted_id}', [StoreMasterController::class, 'update']);
//     Route::delete('/stores/{encrypted_id}', [StoreMasterController::class, 'destroy']);

//     // Online Shop API
//     Route::post('/online-shops', [OnlineShopController::class, 'store']);
//     Route::put('/online-shops/{encrypted_id}', [OnlineShopController::class, 'update']);
//     Route::delete('/online-shops/{encrypted_id}', [OnlineShopController::class, 'destroy']);

//     // Category Master API
//     Route::post('/categories', [CategoryController::class, 'store']);
//     Route::put('/categories/{encrypted_id}', [CategoryController::class, 'update']);
//     Route::delete('/categories/{encrypted_id}', [CategoryController::class, 'destroy']);

//     // Product Master API
//     Route::post('/products', [ProductController::class, 'store']);
//     Route::put('/products/{encrypted_id}', [ProductController::class, 'update']);
//     Route::delete('/products/{encrypted_id}', [ProductController::class, 'destroy']);

//     // Unit Master API
//     Route::post('/units', [UnitController::class, 'store']);
//     Route::put('/units/{encrypted_id}', [UnitController::class, 'update']);
//     Route::delete('/units/{encrypted_id}', [UnitController::class, 'destroy']);

//     // Unit_Convert Master API
//     Route::post('/unit-conversions', [Unit_ConvertController::class, 'store']);
//     Route::put('/unit-conversions/{encrypted_id}', [Unit_ConvertController::class, 'update']);
//     Route::delete('/unit-conversions/{encrypted_id}', [Unit_ConvertController::class, 'destroy']);

//     // Price Master API
//     Route::post('/prices', [PriceController::class, 'store']);
//     Route::put('/prices/{encrypted_id}', [PriceController::class, 'update']);
//     Route::delete('/prices/{encrypted_id}', [PriceController::class, 'destroy']);

//     // purchases API
//     Route::post('/purchases', [PurchasedController::class, 'store']);


// });










// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Offline\Login\RegisterController;
// use App\Http\Controllers\Common\CommonController;
// use App\Http\Controllers\Api\AuthController;


// Route::get('/', function () {
//     return view('Offline/dashboard');
// });

// Route::get('/', function () {
//     return view('Offline/Login/login');
// });



// Route::get('/register', [RegisterController::class, 'show'])->name('register');
// Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
// Route::get('/register/success', [RegisterController::class, 'success'])->name('register.success');


// Route::get('/login', function () { return view('Offline/Login/login'); });



// Route::post('/api/check-username', [RegisterController::class, 'checkUsername']);
// Route::get('/api/get-districts/{state_id}', [CommonController::class, 'getDistricts']);
// Route::get('/api/get-blocks/{district_id}', [CommonController::class, 'getBlocks']);


// Route::group(['prefix' => 'auth'], function () {
//     // Public Route
//     Route::post('login', [AuthController::class, 'login']);
    
//     // Protected Routes (Require JWT Token)
//     Route::group(['middleware' => 'auth:api'], function () {
//         Route::post('logout', [AuthController::class, 'logout']);
//         Route::post('refresh', [AuthController::class, 'refresh']);
//         Route::post('me', [AuthController::class, 'me']);
//     });
// });