<?php

use App\Http\Controllers\Admin\ClosedPeriodController;
use App\Http\Controllers\Admin\CurrentStockController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoodReceiptController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\StockCardController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\StockOutController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UnitConversionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'check.closed.period', 'check.stock.opname.period'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('unit', UnitController::class);
        Route::resource('product', ProductController::class);
        Route::resource('unit_conversion', UnitConversionController::class);
    });

    Route::prefix('inventory')->name('inventory.')->group(function(){
        Route::resource('supplier', SupplierController::class);

        // Purchase Order (PO)
        Route::resource('purchase_order', PurchaseOrderController::class);
        Route::get('purchase_order/{purchase_order}/approval', [PurchaseOrderController::class, 'showApprovalForm'])->name('purchase_order.approval.view');
        Route::patch('purchase_order/{purchase_order}/approval', [PurchaseOrderController::class, 'submitApproval'])->name('purchase_order.approval.submit');

        Route::resource('current_stock', CurrentStockController::class);
        Route::resource('stock_card', StockCardController::class);

        // Good Receipt (Penerimaan Barang)
        Route::resource('good_receipt', GoodReceiptController::class);
        Route::get('good_receipt/{good_receipt}/approval', [GoodReceiptController::class, 'showApprovalForm'])->name('good_receipt.approval.view');
        Route::patch('good_receipt/{good_receipt}/approval', [GoodReceiptController::class, 'submitApproval'])->name('good_receipt.approval.submit');

        // Search Data Purchase Order by No.PO
        Route::get('search_by_no_po', [GoodReceiptController::class, 'search_purchase_order_by_no_po'])->name('search_by_no_po');

        // Stock Out (Pengeluaran Barang)
        Route::resource('stock_out', StockOutController::class);
        Route::get('stock_out/{stock_out}/approval', [StockOutController::class, 'showApprovalForm'])->name('stock_out.approval.view');
        Route::patch('stock_out/{stock_out}/approval', [StockOutController::class, 'submitApproval'])->name('stock_out.approval.submit');

        // Stock Opname (Stock Opname)
        Route::resource('stock_opname', StockOpnameController::class);
        Route::get('stock_opname/{stock_opname}/approval', [StockOpnameController::class, 'showApprovalForm'])->name('stock_opname.approval.view');
        Route::patch('stock_opname/{stock_opname}/approval', [StockOpnameController::class, 'submitApproval'])->name('stock_opname.approval.submit');
        Route::get('stock_opname/{stock_opname}/validator', [StockOpnameController::class, 'showValidatorForm'])->name('stock_opname.validator.view');
        Route::patch('stock_opname/{stock_opname}/validator', [StockOpnameController::class, 'submitValidator'])->name('stock_opname.validator.submit');
    });

    Route::prefix('account')->name('account.')->group(function(){
        Route::resource('closed_period', ClosedPeriodController::class);
        Route::get('closed_period/{closed_period}/approval', [ClosedPeriodController::class, 'showApprovalForm'])->name('closed_period.approval.view');
        Route::patch('closed_period/{closed_period}/approval', [ClosedPeriodController::class, 'submitApproval'])->name('closed_period.approval.submit');
        Route::patch('closed_period/{closed_period}/closed', [ClosedPeriodController::class, 'closedPeriod'])->name('closed_period.closed.submit');
    });

    Route::prefix('transaksi')->name('transaksi.')->group(function() {
        Route::resource('customer', CustomerController::class);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
