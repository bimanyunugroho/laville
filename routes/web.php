<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UnitConversionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('unit', UnitController::class);
        Route::resource('product', ProductController::class);
        Route::resource('unit_conversion', UnitConversionController::class);
    });
    
    Route::prefix('inventory')->name('inventory.')->group(function(){
        Route::resource('supplier', SupplierController::class);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
