<?php

namespace App\Providers;

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
    public function register(): void
    {
        $this->app->bind(UserService::class, function ($app) {
            return new UserService();
        });

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService();
        });
        $this->app->bind(DeviceCategoryService::class, function ($app) {
            return new DeviceCategoryService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
