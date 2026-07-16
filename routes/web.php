<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

use App\Http\Controllers\DrugCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceiptController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    
    Route::resource('drug-categories', DrugCategoryController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('drugs', DrugController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['edit', 'update', 'destroy']);
    Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::resource('goods-receipts', GoodsReceiptController::class)->except(['edit', 'update', 'destroy']);

    Route::group(['middleware' => ['role:admin|petugas-gudang|apoteker']], function () {
        Route::post('distributions/{distribution}/approve', [\App\Http\Controllers\DistributionController::class, 'approve'])->name('distributions.approve');
        Route::post('/distributions/{distribution}/reject', [\App\Http\Controllers\DistributionController::class, 'reject'])->name('distributions.reject');
        Route::get('/distributions/{distribution}/export-pdf', [\App\Http\Controllers\DistributionController::class, 'exportPdf'])->name('distributions.export-pdf');
        Route::resource('distributions', \App\Http\Controllers\DistributionController::class)->except(['edit', 'update', 'destroy']);
    });

    Route::get('/alerts', function () {
        $alerts = \App\Models\ReorderAlert::with('drug')->orderBy('created_at', 'desc')->get();
        return inertia('Inventory/Alerts', ['alerts' => $alerts]);
    })->name('alerts.index');

    Route::post('/alerts/{alert}/read', function (\App\Models\ReorderAlert $alert) {
        $alert->update(['status' => 'read']);
        return back();
    })->name('alerts.read');

    Route::group(['middleware' => ['role:admin|kepala-instalasi|apoteker']], function () {
        Route::get('/abc-dashboard', function () {
            $drugs = \App\Models\Drug::orderBy('abc_class')->get();
            return inertia('Inventory/AbcDashboard', ['drugs' => $drugs]);
        })->name('abc-dashboard.index');

        Route::get('/forecast', [\App\Http\Controllers\ForecastController::class, 'index'])->name('forecast.index');
        Route::post('/forecast/generate', [\App\Http\Controllers\ForecastController::class, 'generate'])->name('forecast.generate');
    });

    Route::get('/stock-card', [\App\Http\Controllers\StockCardController::class, 'index'])->name('stock-card.index');
        
    // Research Report
    Route::group(['middleware' => ['role:admin|kepala-instalasi|apoteker']], function () {
        Route::get('/research-report', [\App\Http\Controllers\ResearchReportController::class, 'index'])->name('research-report.index');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
            Route::get('/receipts', [App\Http\Controllers\ReportController::class, 'exportReceipts'])->name('receipts');
            Route::get('/distributions', [App\Http\Controllers\ReportController::class, 'exportDistributions'])->name('distributions');
            Route::get('/returns', [App\Http\Controllers\ReportController::class, 'exportReturns'])->name('returns');
        });
    });

    Route::group(['middleware' => ['role:admin']], function () {
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });
});

require __DIR__.'/settings.php';
