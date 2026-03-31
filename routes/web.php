<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierProductController;
use App\Http\Controllers\SupplierProductPriceController;

use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderItemController;

use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuRecipeController;

use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesOrderItemController;

use App\Http\Controllers\MarginReportController;

use App\Http\Controllers\BossDashboardController;
use App\Http\Controllers\BossBestSupplierController;
use App\Http\Controllers\BossComplaintAnalyticsController;
use App\Http\Controllers\BossReturnAnalyticsController;



use App\Http\Controllers\ComplaintController;

use App\Http\Controllers\SupplierReviewController;

use App\Http\Controllers\ReturnOrderController;
use App\Http\Controllers\ReturnItemController;
use App\Http\Controllers\UnitConversionsController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\RabItemController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\SupplierPhotoController;
use App\Http\Controllers\SupplierCertificationController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchaseRequestItemController;
use App\Http\Controllers\PurchaseRequestOrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('boss')->name('boss.')->group(function () {
    Route::get('/dashboard', [BossDashboardController::class, 'index'])->name('dashboard');
    Route::get('/best-suppliers', [BossBestSupplierController::class, 'index'])->name('best-suppliers.index');
    Route::get('/complaints', [BossComplaintAnalyticsController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/export', [BossComplaintAnalyticsController::class, 'export'])->name('complaints.export');
    Route::get('/dashboard/export-margin', [BossDashboardController::class, 'exportMarginCsv'])
    ->name('dashboard.export-margin');
    Route::get('/best-suppliers/export', [BossBestSupplierController::class, 'export'])
    ->name('best-suppliers.export');
    Route::get('/returns', [BossReturnAnalyticsController::class, 'index'])->name('returns.index');
    Route::get('/returns/export', [BossReturnAnalyticsController::class, 'export'])->name('returns.export');
});

Route::resource('products', ProductController::class);
Route::resource('suppliers', SupplierController::class);

Route::resource('suppliers.supplier-products', SupplierProductController::class);
Route::resource('supplier-products.prices', SupplierProductPriceController::class);

Route::get('/purchase-orders/suppliers-by-product', [PurchaseOrderController::class, 'suppliersByProduct'])
    ->name('purchase-orders.suppliers-by-product');
Route::resource('purchase-orders', PurchaseOrderController::class);
Route::resource('purchase-orders.items', PurchaseOrderItemController::class);

Route::post('purchase-orders/{purchaseOrder}/receiving', [ReceivingController::class, 'store'])
    ->name('purchase-orders.receiving.store');
Route::resource('receivings', ReceivingController::class)->only(['index', 'show', 'update']);
Route::post('receivings/{receiving}/receive', [ReceivingController::class, 'receive'])
    ->name('receivings.receive');
Route::post('receiving-items/{receivingItem}/proof', [ReceivingController::class, 'uploadProof'])
    ->name('receiving-items.proof.upload');
Route::delete('receiving-items/{receivingItem}/proof', [ReceivingController::class, 'deleteProof'])
    ->name('receiving-items.proof.delete');

Route::resource('menus', MenuController::class);
Route::resource('menus.recipes', MenuRecipeController::class)->only([
    'index','create','store','edit','update','destroy'
]);

Route::resource('sales-orders', SalesOrderController::class);
Route::resource('sales-orders.items', SalesOrderItemController::class)->only(['create','store']);

Route::get('/reports/margin-daily', [MarginReportController::class, 'daily'])->name('reports.margin.daily');
Route::get('/reports/margin-monthly', [MarginReportController::class, 'monthly'])->name('reports.margin.monthly');

Route::get('/reports/margin-daily/export', [MarginReportController::class, 'exportDailyCsv'])->name('reports.margin.daily.export');
Route::get('/reports/margin-monthly/export', [MarginReportController::class, 'exportMonthlyCsv'])->name('reports.margin.monthly.export');

Route::resource('complaints', ComplaintController::class)
    ->only(['index']);
Route::resource('purchase-order-items.complaints', ComplaintController::class)
    ->only(['create', 'store']);

Route::patch('/complaints/{complaint}/resolve', [ComplaintController::class, 'resolve'])
    ->name('complaints.resolve');

Route::patch('/complaints/{complaint}/reopen', [ComplaintController::class, 'reopen'])
    ->name('complaints.reopen');


Route::resource('purchase-orders.reviews', SupplierReviewController::class)
    ->only(['create', 'store', 'show']);

Route::resource('unit-conversions', UnitConversionsController::class);

Route::resource('rabs', RabController::class);
Route::resource('rabs.items', RabItemController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy']);

Route::resource('returns', ReturnOrderController::class);

Route::resource('returns.items', ReturnItemController::class)
    ->only(['create','store','edit','update','destroy']);

Route::resource('certifications', CertificationController::class);

Route::resource('suppliers.photos', SupplierPhotoController::class)
    ->only(['create', 'store', 'destroy']);

Route::resource('suppliers.certifications', SupplierCertificationController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy']);

Route::resource('kitchens', KitchenController::class)
    ->except(['show']);

Route::resource('purchase-requests', PurchaseRequestController::class)
    ->only(['index', 'create', 'store', 'show', 'destroy']);
Route::post('purchase-requests/{purchaseRequest}/confirm', [PurchaseRequestController::class, 'confirm'])
    ->name('purchase-requests.confirm');
Route::post('purchase-requests/{purchaseRequest}/reopen', [PurchaseRequestController::class, 'reopen'])
    ->name('purchase-requests.reopen');
Route::patch('purchase-requests/{purchaseRequest}/items/{item}', [PurchaseRequestItemController::class, 'update'])
    ->name('purchase-requests.items.update');

Route::get('purchase-requests/{purchaseRequest}/orders/create', [PurchaseRequestOrderController::class, 'create'])
    ->name('purchase-requests.orders.create');
Route::post('purchase-requests/{purchaseRequest}/orders', [PurchaseRequestOrderController::class, 'store'])
    ->name('purchase-requests.orders.store');
