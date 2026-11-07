<?php

use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\StockAttributeController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('dashboard');
Route::view('/inventory', 'inventory')->name('inventory.index');
Route::get('/inventory/products/{product}/image', [ProductController::class, 'image'])->whereNumber('product')->name('inventory.products.image');
Route::resource('inventory/products', ProductController::class)
    ->names('inventory.products')
    ->only(['create', 'store', 'edit', 'update', 'destroy']);
Route::resource('inventory/stock', StockAttributeController::class)
    ->parameters(['stock' => 'attribute'])
    ->names('inventory.stock')
    ->only(['index', 'store', 'edit', 'update', 'destroy']);
Route::view('/purchase', 'purchase')->name('purchase.index');
Route::resource('purchase/invoices', PurchaseInvoiceController::class)->names('purchase.invoices')->only(['create', 'store', 'show']);
Route::view('/sales', 'sales')->name('sales.index');
Route::get('/sales/invoices/{source}/{id}/print', InvoicePrintController::class)
    ->whereIn('source', ['invoice', 'legacy'])->whereNumber('id')->name('sales.invoices.print');
Route::view('/customers-suppliers', 'contacts')->name('contacts.index');
Route::view('/accounting', 'accounting')->name('accounting.index');
Route::view('/reports', 'reports')->name('reports.index');
Route::view('/settings', 'settings')->name('settings.index');
Route::post('/settings/company', [CompanySettingController::class, 'update'])->name('settings.company.update');
Route::get('/settings/company/logo', [CompanySettingController::class, 'logo'])->name('settings.company.logo');

foreach (['suppliers', 'customers'] as $contactType) {
    Route::resource($contactType, SupplierController::class)->parameters([$contactType => 'contact'])->only(['index', 'store', 'edit', 'update', 'destroy']);
}
