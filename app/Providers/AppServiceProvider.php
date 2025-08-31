<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Employee;
use App\Observers\CompanyObserver;
use App\Observers\EmployeeObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);
        // Register Company Observer to auto-create trial subscription
        Company::observe(CompanyObserver::class);
        // Register observer to auto-create leave balances when a new employee is added
        Employee::observe(EmployeeObserver::class);
    }
}
