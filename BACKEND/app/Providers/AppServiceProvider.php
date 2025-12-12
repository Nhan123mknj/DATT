<?php

namespace App\Providers;

use App\Models\CategoriesDevice;
use App\Observers\CategoryObserver;
use App\Services\AuthService;
use App\Services\DeviceCategory;
use App\Services\DeviceCategoryService;
use Illuminate\Support\ServiceProvider;
use App\Services\UserService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\DeviceUnits::observe(\App\Observers\DeviceUnitObserver::class);
    }
}
