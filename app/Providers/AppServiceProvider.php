<?php

namespace App\Providers;

use App\BusinessRecords;
use App\Models\CompanySetting;
use App\Models\StockAttribute;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('inventory', function ($view): void {
            $options = Schema::hasTable('stock_attributes')
                ? StockAttribute::whereIn('type', ['brand', 'category', 'model'])->orderBy('name')->get()->groupBy('type')
                : null;

            $view->with('stockOptions', $options);
        });

        View::composer(['welcome', 'inventory', 'purchase', 'sales', 'contacts', 'accounting', 'reports'], function ($view): void {
            $view->with('businessRecords', app(BusinessRecords::class)->forView($view->name()));
        });

        View::composer(['layouts.app', 'settings', 'sales'], function ($view): void {
            $view->with('companyProfile', CompanySetting::profile());
        });
    }
}
