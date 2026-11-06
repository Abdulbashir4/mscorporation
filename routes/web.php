<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('dashboard');
Route::view('/inventory', 'inventory')->name('inventory.index');
Route::view('/purchase', 'purchase')->name('purchase.index');
Route::view('/sales', 'sales')->name('sales.index');
Route::view('/customers-suppliers', 'contacts')->name('contacts.index');
Route::view('/accounting', 'accounting')->name('accounting.index');
Route::view('/reports', 'reports')->name('reports.index');
