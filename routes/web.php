<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;

// Auth
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::resource('products', ProductController::class)->except(['show', 'create', 'edit']);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    // Variants
    Route::resource('variants', VariantController::class)->except(['show', 'create', 'edit']);

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');

    // Sales (online)
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::patch('/sales/{sale}/status', [SaleController::class, 'updateStatus'])->name('sales.updateStatus');
    Route::post('/sales/{sale}/return', [SaleController::class, 'returnItem'])->name('sales.return');

    // Cashier
    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier/checkout', [CashierController::class, 'checkout'])->name('cashier.checkout');
    Route::get('/cashier/{sale}/receipt', [CashierController::class, 'receipt'])->name('cashier.receipt');

    // Transactions (history)
    Route::get('/transactions', [SaleController::class, 'index'])->name('transactions.index');

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['show', 'create', 'edit']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf/{type}', [ReportController::class, 'pdf'])->name('reports.pdf');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
