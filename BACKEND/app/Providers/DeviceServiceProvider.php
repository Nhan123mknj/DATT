<?php

namespace App\Providers;

use App\Services\DeviceCategoryService;
use App\Services\DeviceService;
use Illuminate\Support\ServiceProvider;

class DeviceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DeviceService::class, function () {
            return new DeviceService();
        });
        $this->app->singleton(DeviceCategoryService::class, function () {
            return new DeviceCategoryService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
