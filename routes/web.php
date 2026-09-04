<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| Routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Public Routes - Home/Welcome
Route::get('/', function () {
    if (auth()->check()) {
        // Authenticated users (admin) go to dashboard
        return redirect()->route('admin.dashboard');
    }
    // Guests see welcome page
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest Routes (Authentication)
|--------------------------------------------------------------------------
|
| Routes accessible only to guests (unauthenticated users).
| Authenticated users will be redirected to the dashboard.
|
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| Routes accessible only to authenticated users.
|
*/

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile/image', [ProfileController::class, 'deleteProfileImage'])->name('profile.image.delete');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| All admin panel routes are prefixed with 'admin' and grouped under
| the 'admin' name prefix for consistent route naming.
| Protected by authentication middleware.
|
*/

Route::middleware(['auth', 'user_status'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Setup helper route (creates missing tables)
    Route::get('/setup/create-tables', [\App\Http\Controllers\Admin\SetupController::class, 'createMissingTables'])->name('setup.createTables');

    // ========== MANAGEMENT FEATURES REMOVED IN PHASE 1 & 2 ==========
    // Removed: User, Company, Category, Product, Warehouse, Inventory, Stock Test, Customer, Supplier management
    // Models are preserved for Sales/Purchases/Reports (where applicable)
    // =========================================================

    // ============ PURCHASE RETURNS ============
    Route::prefix('purchase-returns')->name('purchase-returns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'index'])
            ->name('index')
            ->middleware('permission:purchases.view');
        
        Route::get('/create', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'create'])
            ->name('create')
            ->middleware('permission:purchases.create');
        
        Route::post('/', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'store'])
            ->name('store')
            ->middleware('permission:purchases.create');
        
        Route::get('/{purchaseReturn}', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'show'])
            ->name('show')
            ->middleware('permission:purchases.view');
        
        Route::post('/{purchaseReturn}/confirm', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'confirm'])
            ->name('confirm')
            ->middleware('permission:purchases.create');
        
        Route::post('/{purchaseReturn}/cancel', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'cancel'])
            ->name('cancel')
            ->middleware('permission:purchases.create');
        
        Route::delete('/{purchaseReturn}', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:purchases.delete');
        
        // AJAX endpoint for loading purchases
        Route::get('/purchases/search', [\App\Http\Controllers\Admin\PurchaseReturnController::class, 'getPurchases'])
            ->name('purchases')
            ->middleware('permission:purchases.view');
    });
    // ============ END PURCHASE RETURNS ============

    // Purchase Management
    Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)
        ->middleware('permission:purchases.view');

    // AJAX: Get all suppliers (for single-page create form)
    Route::get('/suppliers/all', [\App\Http\Controllers\Admin\SupplierController::class, 'getAll'])
        ->name('suppliers.getAll')
        ->middleware('permission:purchases.create');

    // AJAX: Get all products (for single-page create form)
    Route::get('/products/all', [\App\Http\Controllers\Admin\ProductController::class, 'getAll'])
        ->name('products.getAll')
        ->middleware('permission:purchases.create');

    // AJAX: Search suppliers
    Route::get('/suppliers/search', [\App\Http\Controllers\Admin\SupplierController::class, 'search'])
        ->name('suppliers.search')
        ->middleware('permission:purchases.create');

    // AJAX: Search products
    Route::get('/products/search', [\App\Http\Controllers\Admin\ProductController::class, 'search'])
        ->name('products.search')
        ->middleware('permission:purchases.create');

    // AJAX: Create supplier inline
    Route::post('/suppliers/ajax', [\App\Http\Controllers\Admin\SupplierController::class, 'storeAjax'])
        ->name('suppliers.storeAjax')
        ->middleware('permission:suppliers.create');

    // Supplier Management
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)
        ->middleware('permission:suppliers.view');

    // AJAX: Create product inline
    Route::post('/products/ajax', [\App\Http\Controllers\Admin\ProductController::class, 'storeAjax'])
        ->name('products.storeAjax')
        ->middleware('permission:purchases.create');

    // Product Management
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)
        ->middleware('permission:products.view');

    // AJAX: Get all products (for single-page create form) - MUST come BEFORE resource routes
    Route::get('/purchases-products', [\App\Http\Controllers\Admin\PurchaseController::class, 'getProducts'])
        ->name('purchases.getProducts')
        ->middleware('permission:purchases.create');

    // Purchase actions
    Route::post('/purchases/{purchase}/confirm', [\App\Http\Controllers\Admin\PurchaseController::class, 'confirm'])
        ->name('purchases.confirm')
        ->middleware('permission:purchases.approve');
    
    Route::post('/purchases/{purchase}/cancel', [\App\Http\Controllers\Admin\PurchaseController::class, 'cancel'])
        ->name('purchases.cancel')
        ->middleware('permission:purchases.cancel');

    // Purchase items
    Route::post('/purchases/{purchase}/items', [\App\Http\Controllers\Admin\PurchaseController::class, 'addItem'])
        ->name('purchases.addItem')
        ->middleware('permission:purchases.update');
    
    Route::put('/purchases/{item}', [\App\Http\Controllers\Admin\PurchaseController::class, 'updateItem'])
        ->name('purchases.updateItem')
        ->middleware('permission:purchases.update');
    
    Route::delete('/purchases/{item}', [\App\Http\Controllers\Admin\PurchaseController::class, 'removeItem'])
        ->name('purchases.removeItem')
        ->middleware('permission:purchases.update');

    // Purchase expenses
    Route::post('/purchases/{purchase}/expenses', [\App\Http\Controllers\Admin\PurchaseController::class, 'updateExpenses'])
        ->name('purchases.updateExpenses')
        ->middleware('permission:purchases.update');

    // Purchase print
    Route::get('/purchases/{purchase}/print', [\App\Http\Controllers\Admin\PurchaseController::class, 'print'])
        ->name('purchases.print')
        ->middleware('permission:purchases.view');

    // ============ SALE REPORTS ============
    Route::prefix('reports/sales')->name('reports.sales.')->middleware('permission:sales.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SaleReportController::class, 'index'])->name('index');
        Route::get('/{sale}', [\App\Http\Controllers\Admin\SaleReportController::class, 'show'])->name('show');
        Route::delete('/bulk-delete', [\App\Http\Controllers\Admin\SaleReportController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('permission:sales.delete');
    });
    // ============ END SALE REPORTS ============

    // ============ PURCHASE REPORTS ============
    Route::prefix('reports/purchases')->name('reports.purchases.')->middleware('permission:purchases.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PurchaseReportController::class, 'index'])->name('index');
        Route::get('/{purchase}', [\App\Http\Controllers\Admin\PurchaseReportController::class, 'show'])->name('show');
        Route::delete('/bulk-delete', [\App\Http\Controllers\Admin\PurchaseReportController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('permission:purchases.delete');
    });
    // ============ END PURCHASE REPORTS ============

    // ============ PROFIT & LOSS REPORTS ============
    Route::prefix('reports/profit-loss')->name('reports.profit-loss.')->middleware('permission:sales.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ProfitLossController::class, 'index'])->name('index');
        Route::get('/{sale}', [\App\Http\Controllers\Admin\ProfitLossController::class, 'show'])->name('show');
    });
    // ============ END PROFIT & LOSS REPORTS ============

    // Sales Management
    // AJAX endpoints for returns (must come before resource route)
    Route::get('/sales/search-for-return', [\App\Http\Controllers\Admin\SaleReturnController::class, 'searchSales'])
        ->name('sales.searchForReturn')
        ->middleware('permission:sales.view');
    
    Route::get('/sales/{sale}/return-summary', [\App\Http\Controllers\Admin\SaleReturnController::class, 'getSaleReturnSummary'])
        ->name('sales.returnSummary')
        ->middleware('permission:sales.view');
    
    Route::get('/sales/{sale}/returns', [\App\Http\Controllers\Admin\SaleReturnController::class, 'getSaleReturns'])
        ->name('sales.returns')
        ->middleware('permission:sales.view');

    Route::resource('sales', \App\Http\Controllers\Admin\SalesController::class)
        ->middleware('permission:sales.view')
        ->whereNumber('sale');

    // Sale actions
    Route::post('/sales/{sale}/confirm', [\App\Http\Controllers\Admin\SalesController::class, 'confirm'])
        ->name('sales.confirm')
        ->middleware('permission:sales.approve');
    
    Route::post('/sales/{sale}/cancel', [\App\Http\Controllers\Admin\SalesController::class, 'cancel'])
        ->name('sales.cancel')
        ->middleware('permission:sales.cancel');

    // Update sale with items (for single-page edit form)
    Route::put('/sales/{sale}/update-items', [\App\Http\Controllers\Admin\SalesController::class, 'updateWithItems'])
        ->name('sales.updateWithItems')
        ->middleware('permission:sales.update');

    // Sale items
    Route::post('/sales/{sale}/items', [\App\Http\Controllers\Admin\SalesController::class, 'addItem'])
        ->name('sales.addItem')
        ->middleware('permission:sales.update');
    
    Route::put('/sales/{item}', [\App\Http\Controllers\Admin\SalesController::class, 'updateItem'])
        ->name('sales.updateItem')
        ->middleware('permission:sales.update');
    
    Route::delete('/sales/{item}', [\App\Http\Controllers\Admin\SalesController::class, 'removeItem'])
        ->name('sales.removeItem')
        ->middleware('permission:sales.update');

    // Sale discount
    Route::post('/sales/{sale}/discount', [\App\Http\Controllers\Admin\SalesController::class, 'updateDiscount'])
        ->name('sales.updateDiscount')
        ->middleware('permission:sales.update');

    // Payments and printing
    Route::post('/sales/{sale}/payment', [\App\Http\Controllers\Admin\SalesController::class, 'recordPayment'])
        ->name('sales.recordPayment')
        ->middleware('permission:sales.approve');

    Route::get('/sales/{sale}/print', [\App\Http\Controllers\Admin\SalesController::class, 'printInvoice'])
        ->name('sales.print-invoice')
        ->middleware('permission:sales.view');

    // Stock check
    Route::post('/sales/check-stock', [\App\Http\Controllers\Admin\SalesController::class, 'checkStock'])
        ->name('sales.checkStock')
        ->middleware('permission:sales.create');

    // Customer and Product search (AJAX endpoints for single-page sale)
    Route::get('/customers/search', [\App\Http\Controllers\Admin\SalesController::class, 'searchCustomers'])
        ->name('customers.search')
        ->middleware('permission:sales.create');
    
    Route::get('/customers/all', [\App\Http\Controllers\Admin\SalesController::class, 'getAllCustomers'])
        ->name('customers.all')
        ->middleware('permission:sales.create');
    
    Route::post('/customers/ajax', [\App\Http\Controllers\Admin\SalesController::class, 'storeWalkinCustomer'])
        ->name('customers.storeAjax')
        ->middleware('permission:sales.create');
    
    Route::get('/products/search', [\App\Http\Controllers\Admin\SalesController::class, 'searchProducts'])
        ->name('products.search')
        ->middleware('permission:sales.create');

    // Family Management (AJAX endpoints for sales)
    Route::post('/families', [\App\Http\Controllers\Admin\FamilyController::class, 'store'])
        ->name('families.store')
        ->middleware('permission:sales.create');
    
    Route::get('/families/search', [\App\Http\Controllers\Admin\FamilyController::class, 'search'])
        ->name('families.search')
        ->middleware('permission:sales.create');
    
    Route::get('/families/all', [\App\Http\Controllers\Admin\FamilyController::class, 'getAll'])
        ->name('families.getAll')
        ->middleware('permission:sales.create');

    // AJAX: Get warehouse products (for single-page create form)
    Route::get('/sales/warehouse/{warehouse}/products', [\App\Http\Controllers\Admin\SalesController::class, 'getWarehouseProducts'])
        ->name('sales.warehouseProducts')
        ->middleware('permission:sales.create');
    // Sales Returns Management
    Route::prefix('sale-returns')->name('sale-returns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SaleReturnController::class, 'index'])
            ->name('index')
            ->middleware('permission:sales.view');
        
        Route::get('/create', [\App\Http\Controllers\Admin\SaleReturnController::class, 'create'])
            ->name('create')
            ->middleware('permission:sales.create');
        
        Route::post('/', [\App\Http\Controllers\Admin\SaleReturnController::class, 'store'])
            ->name('store')
            ->middleware('permission:sales.create');
        
        Route::get('/{return}', [\App\Http\Controllers\Admin\SaleReturnController::class, 'show'])
            ->name('show')
            ->middleware('permission:sales.view');
        
        Route::post('/{return}/confirm', [\App\Http\Controllers\Admin\SaleReturnController::class, 'confirm'])
            ->name('confirm')
            ->middleware('permission:sales.approve');
        
        Route::post('/{return}/cancel', [\App\Http\Controllers\Admin\SaleReturnController::class, 'cancel'])
            ->name('cancel')
            ->middleware('permission:sales.cancel');
    });


    

    // AJAX endpoints for returns


    // ========== PHASE 5: CUSTOMER UDHAR AND PAYMENT MANAGEMENT ==========
    
    // Udhar Management (Customer Outstanding/Credit)
    Route::prefix('udhar')->name('udhar.')->middleware('permission:sales.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UdharController::class, 'index'])
            ->name('index');
        
        Route::get('/customer/{customer}', [\App\Http\Controllers\Admin\UdharController::class, 'showCustomer'])
            ->name('show-customer');
        
        Route::get('/family/{family}', [\App\Http\Controllers\Admin\UdharController::class, 'showFamily'])
            ->name('show-family');
        
        // Legacy route for backward compatibility
        Route::get('/{customer}', [\App\Http\Controllers\Admin\UdharController::class, 'show'])
            ->name('show')
            ->where('customer', '[0-9]+');
        
        // Payment management
        Route::post('/customer/{customer}/receive-payment', [\App\Http\Controllers\Admin\UdharController::class, 'receiveIndividualPayment'])
            ->name('receive-individual-payment')
            ->middleware('permission:sales.create');
        
        Route::post('/family/{family}/receive-payment', [\App\Http\Controllers\Admin\UdharController::class, 'receiveFamilyPayment'])
            ->name('receive-family-payment')
            ->middleware('permission:sales.create');
        
        Route::post('/sales/{sale}/receive-payment', [\App\Http\Controllers\Admin\UdharController::class, 'receivePayment'])
            ->name('receive-payment');
        
        Route::get('/sales/{sale}/payments', [\App\Http\Controllers\Admin\UdharController::class, 'getSalePayments'])
            ->name('sale-payments');
        
        Route::get('/statistics', [\App\Http\Controllers\Admin\UdharController::class, 'getStatistics'])
            ->name('statistics');
    });

    // Customer Account Statements
    Route::prefix('customers')->name('customers.')->middleware('permission:sales.view')->group(function () {
        Route::get('/{customer}/statement', [\App\Http\Controllers\Admin\CustomerAccountController::class, 'statement'])
            ->name('statement');
        
        Route::get('/{customer}/statement/export', [\App\Http\Controllers\Admin\CustomerAccountController::class, 'exportStatement'])
            ->name('statement.export');
    });

    // ========== END PHASE 5 ROUTES ==========

    // Udhar Management (Legacy - can be removed if not used)
    Route::prefix('udhar')->name('udhar.')->middleware('permission:udhar.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UdharController::class, 'index'])
            ->name('index');
        
        Route::get('/{customer}', [\App\Http\Controllers\Admin\UdharController::class, 'details'])
            ->name('details');
        
        Route::get('/{customer}/ledger', [\App\Http\Controllers\Admin\UdharController::class, 'ledger'])
            ->name('ledger');
        
        Route::get('/{customer}/print', [\App\Http\Controllers\Admin\UdharController::class, 'printStatement'])
            ->name('print');
        
        Route::get('/{customer}/history', [\App\Http\Controllers\Admin\UdharController::class, 'transactionHistory'])
            ->name('transaction-history');
        
        Route::post('/{customer}/payment', [\App\Http\Controllers\Admin\UdharController::class, 'recordPayment'])
            ->name('recordPayment')
            ->middleware('permission:udhar.create');
    });

    // ========== SUPPLIER PAYABLES ==========
    Route::prefix('supplier-payables')->name('supplier-payables.')->middleware('permission:purchases.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SupplierPayableController::class, 'index'])->name('index');
        Route::get('/{supplier}', [\App\Http\Controllers\Admin\SupplierPayableController::class, 'show'])->name('show');
        Route::get('/{supplier}/history', [\App\Http\Controllers\Admin\SupplierPayableController::class, 'history'])->name('history');
        Route::post('/{supplier}/payment', [\App\Http\Controllers\Admin\SupplierPayableController::class, 'payment'])
            ->middleware('permission:purchases.create')
            ->name('payment');
    });

    // Notifications Management
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])
            ->name('index');
        
        // API Endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/unread-count', [\App\Http\Controllers\Admin\NotificationController::class, 'getUnreadCount'])
                ->name('unread-count');
            
            Route::get('/recent', [\App\Http\Controllers\Admin\NotificationController::class, 'getRecent'])
                ->name('recent');
            
            Route::post('/{notification}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])
                ->name('mark-read');
            
            Route::post('/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])
                ->name('mark-all-read');
            
            Route::post('/{notification}/delete', [\App\Http\Controllers\Admin\NotificationController::class, 'delete'])
                ->name('delete');
            
            Route::get('/preferences', [\App\Http\Controllers\Admin\NotificationController::class, 'getPreferences'])
                ->name('preferences');
            
            Route::post('/preferences/update', [\App\Http\Controllers\Admin\NotificationController::class, 'updatePreferences'])
                ->name('preferences-update');
        });
    });

    // Future module routes will be added here following this pattern:
    // Route::resource('dealers', DealerController::class);
    // Route::resource('returns', ReturnController::class);
});

// Setup routes - NO AUTHENTICATION REQUIRED for table creation
Route::get('/setup/create-tables', [\App\Http\Controllers\Admin\SetupController::class, 'createMissingTables'])->name('setup.create.tables');
