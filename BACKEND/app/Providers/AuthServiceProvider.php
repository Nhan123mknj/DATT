<?php

namespace App\Providers;

use App\Models\Borrows;
use App\Policies\BorrowPolicy;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
   
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class, function () {
            return new AuthService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
