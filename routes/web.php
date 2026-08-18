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

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->middleware('permission:users.view');
    
    // User status actions
    Route::patch('/users/{user}/activate', [\App\Http\Controllers\Admin\UserController::class, 'activate'])
        ->name('users.activate')
        ->middleware('permission:users.update');
    
    Route::patch('/users/{user}/deactivate', [\App\Http\Controllers\Admin\UserController::class, 'deactivate'])
        ->name('users.deactivate')
        ->middleware('permission:users.update');
    
    Route::patch('/users/{user}/suspend', [\App\Http\Controllers\Admin\UserController::class, 'suspend'])
        ->name('users.suspend')
        ->middleware('permission:users.update');
    
    Route::patch('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])
        ->name('users.reset-password')
        ->middleware('permission:users.update');

    // Company Management
    Route::resource('companies', \App\Http\Controllers\Admin\CompanyController::class)
        ->middleware('permission:companies.view');
    
    // Company status actions
    Route::patch('/companies/{company}/activate', [\App\Http\Controllers\Admin\CompanyController::class, 'activate'])
        ->name('companies.activate')
        ->middleware('permission:companies.update');
    
    Route::patch('/companies/{company}/deactivate', [\App\Http\Controllers\Admin\CompanyController::class, 'deactivate'])
        ->name('companies.deactivate')
        ->middleware('permission:companies.update');

    // Product Management
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)
        ->middleware('permission:products.view');
    
    // Product status actions
    Route::patch('/products/{product}/activate', [\App\Http\Controllers\Admin\ProductController::class, 'activate'])
        ->name('products.activate')
        ->middleware('permission:products.update');
    
    Route::patch('/products/{product}/deactivate', [\App\Http\Controllers\Admin\ProductController::class, 'deactivate'])
        ->name('products.deactivate')
        ->middleware('permission:products.update');

    // Warehouse Management
    Route::resource('warehouses', \App\Http\Controllers\Admin\WarehouseController::class)
        ->middleware('permission:warehouses.view');
    
    // Warehouse status actions
    Route::patch('/warehouses/{warehouse}/activate', [\App\Http\Controllers\Admin\WarehouseController::class, 'activate'])
        ->name('warehouses.activate')
        ->middleware('permission:warehouses.update');
    
    Route::patch('/warehouses/{warehouse}/deactivate', [\App\Http\Controllers\Admin\WarehouseController::class, 'deactivate'])
        ->name('warehouses.deactivate')
        ->middleware('permission:warehouses.update');
    
    // Warehouse inventory
    Route::get('/warehouses/{warehouse}/inventory', [\App\Http\Controllers\Admin\WarehouseController::class, 'inventory'])
        ->name('warehouses.inventory')
        ->middleware('permission:warehouses.view');

    // Inventory Management
    Route::get('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])
        ->name('inventory.index')
        ->middleware('permission:inventory.view');
    
    Route::get('/inventory/movements', [\App\Http\Controllers\Admin\InventoryController::class, 'movements'])
        ->name('inventory.movements')
        ->middleware('permission:inventory.view');
    
    Route::get('/inventory/low-stock', [\App\Http\Controllers\Admin\InventoryController::class, 'lowStock'])
        ->name('inventory.low-stock')
        ->middleware('permission:inventory.view');

    // Stock Service Testing (Development Only - Disable in Production)
    if (!app()->isProduction()) {
        Route::prefix('stock-test')->name('stock-test.')->middleware('permission:inventory.manage')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\StockTestController::class, 'index'])
                ->name('index');
            Route::post('/add', [\App\Http\Controllers\Admin\StockTestController::class, 'addStock'])
                ->name('add');
            Route::post('/remove', [\App\Http\Controllers\Admin\StockTestController::class, 'removeStock'])
                ->name('remove');
            Route::post('/transfer', [\App\Http\Controllers\Admin\StockTestController::class, 'transferStock'])
                ->name('transfer');
            Route::post('/adjust', [\App\Http\Controllers\Admin\StockTestController::class, 'adjustStock'])
                ->name('adjust');
            Route::post('/check', [\App\Http\Controllers\Admin\StockTestController::class, 'checkStock'])
                ->name('check');
        });
    }

    // Supplier Management
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)
        ->middleware('permission:suppliers.view');
    
    // Supplier status actions
    Route::patch('/suppliers/{supplier}/activate', [\App\Http\Controllers\Admin\SupplierController::class, 'activate'])
        ->name('suppliers.activate')
        ->middleware('permission:suppliers.update');
    
    Route::patch('/suppliers/{supplier}/deactivate', [\App\Http\Controllers\Admin\SupplierController::class, 'deactivate'])
        ->name('suppliers.deactivate')
        ->middleware('permission:suppliers.update');

    // Customer Management
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)
        ->middleware('permission:customers.view');
    
    // Customer status actions
    Route::patch('/customers/{customer}/activate', [\App\Http\Controllers\Admin\CustomerController::class, 'activate'])
        ->name('customers.activate')
        ->middleware('permission:customers.update');
    
    Route::patch('/customers/{customer}/deactivate', [\App\Http\Controllers\Admin\CustomerController::class, 'deactivate'])
        ->name('customers.deactivate')
        ->middleware('permission:customers.update');

    // Purchase Management
    Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)
        ->middleware('permission:purchases.view');

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

    // Sales Management
    Route::resource('sales', \App\Http\Controllers\Admin\SalesController::class)
        ->middleware('permission:sales.view');

    // Sale actions
    Route::post('/sales/{sale}/confirm', [\App\Http\Controllers\Admin\SalesController::class, 'confirm'])
        ->name('sales.confirm')
        ->middleware('permission:sales.approve');
    
    Route::post('/sales/{sale}/cancel', [\App\Http\Controllers\Admin\SalesController::class, 'cancel'])
        ->name('sales.cancel')
        ->middleware('permission:sales.cancel');

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

    // Udhar Management (Credit/Outstanding)
    Route::prefix('udhar')->name('udhar.')->middleware('permission:udhar.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UdharController::class, 'index'])
            ->name('index');
        
        Route::get('/{customer}', [\App\Http\Controllers\Admin\UdharController::class, 'details'])
            ->name('details');
        
        Route::get('/{customer}/ledger', [\App\Http\Controllers\Admin\UdharController::class, 'ledger'])
            ->name('ledger');
        
        Route::post('/{customer}/payment', [\App\Http\Controllers\Admin\UdharController::class, 'recordPayment'])
            ->name('recordPayment')
            ->middleware('permission:udhar.create');
    });

    // Payables Management (Supplier Outstanding)
    Route::prefix('payables')->name('payables.')->middleware('permission:payables.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PayableController::class, 'index'])
            ->name('index');
        
        Route::get('/aging', [\App\Http\Controllers\Admin\PayableController::class, 'aging'])
            ->name('aging');
        
        Route::get('/{supplier}', [\App\Http\Controllers\Admin\PayableController::class, 'details'])
            ->name('details');
        
        Route::get('/{supplier}/ledger', [\App\Http\Controllers\Admin\PayableController::class, 'ledger'])
            ->name('ledger');
        
        Route::post('/{supplier}/payment', [\App\Http\Controllers\Admin\PayableController::class, 'recordPayment'])
            ->name('recordPayment')
            ->middleware('permission:payables.create');
    });

    // Stock Transfer Management
    Route::resource('stock-transfers', \App\Http\Controllers\Admin\StockTransferController::class)
        ->middleware('permission:transfers.view');

    // Transfer actions
    Route::post('/stock-transfers/{stock_transfer}/submit', [\App\Http\Controllers\Admin\StockTransferController::class, 'submitForApproval'])
        ->name('stock-transfers.submit')
        ->middleware('permission:transfers.create');
    
    Route::post('/stock-transfers/{stock_transfer}/approve', [\App\Http\Controllers\Admin\StockTransferController::class, 'approve'])
        ->name('stock-transfers.approve')
        ->middleware('permission:transfers.approve');
    
    Route::post('/stock-transfers/{stock_transfer}/dispatch', [\App\Http\Controllers\Admin\StockTransferController::class, 'dispatch'])
        ->name('stock-transfers.dispatch')
        ->middleware('permission:transfers.approve');
    
    Route::post('/stock-transfers/{stock_transfer}/in-transit', [\App\Http\Controllers\Admin\StockTransferController::class, 'markInTransit'])
        ->name('stock-transfers.in-transit')
        ->middleware('permission:transfers.receive');
    
    Route::post('/stock-transfers/{stock_transfer}/receive', [\App\Http\Controllers\Admin\StockTransferController::class, 'receive'])
        ->name('stock-transfers.receive')
        ->middleware('permission:transfers.receive');
    
    Route::post('/stock-transfers/{stock_transfer}/cancel', [\App\Http\Controllers\Admin\StockTransferController::class, 'cancel'])
        ->name('stock-transfers.cancel')
        ->middleware('permission:transfers.create');

    // Transfer items
    Route::post('/stock-transfers/{stock_transfer}/items', [\App\Http\Controllers\Admin\StockTransferController::class, 'addItem'])
        ->name('stock-transfers.addItem')
        ->middleware('permission:transfers.create');
    
    Route::put('/stock-transfers/items/{stock_transfer_item}', [\App\Http\Controllers\Admin\StockTransferController::class, 'updateItem'])
        ->name('stock-transfers.updateItem')
        ->middleware('permission:transfers.create');
    
    Route::delete('/stock-transfers/items/{stock_transfer_item}', [\App\Http\Controllers\Admin\StockTransferController::class, 'removeItem'])
        ->name('stock-transfers.removeItem')
        ->middleware('permission:transfers.create');

    // Stock check for transfers
    Route::get('/stock-transfers/check-stock', [\App\Http\Controllers\Admin\StockTransferController::class, 'checkStock'])
        ->name('stock-transfers.checkStock')
        ->middleware('permission:transfers.create');

    // Reports - COMMENTED OUT (Views not implemented yet)
    /*
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        // Inventory Reports
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'inventoryIndex'])->name('index');
            Route::get('/current-stock', [\App\Http\Controllers\Admin\ReportsController::class, 'currentStock'])->name('current-stock');
            Route::get('/warehouse-stock', [\App\Http\Controllers\Admin\ReportsController::class, 'warehouseStock'])->name('warehouse-stock');
            Route::get('/stock-movements', [\App\Http\Controllers\Admin\ReportsController::class, 'stockMovements'])->name('stock-movements');
        });

        // Sales Reports
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'salesIndex'])->name('index');
            Route::get('/daily', [\App\Http\Controllers\Admin\ReportsController::class, 'dailySales'])->name('daily');
            Route::get('/product-wise', [\App\Http\Controllers\Admin\ReportsController::class, 'productWiseSales'])->name('product-wise');
            Route::get('/customer-wise', [\App\Http\Controllers\Admin\ReportsController::class, 'customerWiseSales'])->name('customer-wise');
            Route::get('/warehouse-wise', [\App\Http\Controllers\Admin\ReportsController::class, 'warehouseSales'])->name('warehouse-wise');
        });

        // Purchase Reports
        Route::prefix('purchase')->name('purchase.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'purchaseIndex'])->name('index');
            Route::get('/purchases', [\App\Http\Controllers\Admin\ReportsController::class, 'purchases'])->name('purchases');
            Route::get('/supplier-wise', [\App\Http\Controllers\Admin\ReportsController::class, 'supplierWisePurchases'])->name('supplier-wise');
            Route::get('/product-wise', [\App\Http\Controllers\Admin\ReportsController::class, 'productWisePurchases'])->name('product-wise');
        });

        // Customer Reports
        Route::prefix('customer')->name('customer.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'customerIndex'])->name('index');
            Route::get('/outstanding', [\App\Http\Controllers\Admin\ReportsController::class, 'customerOutstanding'])->name('outstanding');
            Route::get('/{customer}/payment-history', [\App\Http\Controllers\Admin\ReportsController::class, 'customerPaymentHistory'])->name('payment-history');
            Route::get('/{customer}/ledger', [\App\Http\Controllers\Admin\ReportsController::class, 'customerLedger'])->name('ledger');
        });
    });
    */

    // Future module routes will be added here following this pattern:
    // Route::resource('dealers', DealerController::class);
    // Route::resource('returns', ReturnController::class);
});
