<?php

namespace App\Providers;

use App\Models\Borrows;
use App\Models\BorrowsDetail;
use App\Observers\BorrowsDetailObserver;
use App\Policies\BorrowPolicy;
use App\Services\BorrowService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BorrowServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BorrowService::class, function () {
            return new BorrowService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        BorrowsDetail::observe(BorrowsDetailObserver::class);
        Gate::policy(Borrows::class, BorrowPolicy::class);
    }
}
