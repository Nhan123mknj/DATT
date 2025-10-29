<?php

namespace App\Observers;

use App\Models\CategoriesDevice;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Handle the CategoriesDevice "created" event.
     */
    public function created(CategoriesDevice $categoriesDevice): void
    {
        $this->clearCache();
    }

    /**
     * Handle the CategoriesDevice "updated" event.
     */
    public function updated(CategoriesDevice $categoriesDevice): void
    {
        $this->clearCache();
    }

    /**
     * Handle the CategoriesDevice "deleted" event.
     */
    public function deleted(CategoriesDevice $categoriesDevice): void
    {
        $this->clearCache();
    }

    /**
     * Handle the CategoriesDevice "restored" event.
     */
    public function restored(CategoriesDevice $categoriesDevice): void
    {
        //
    }

    /**
     * Handle the CategoriesDevice "force deleted" event.
     */
    public function forceDeleted(CategoriesDevice $categoriesDevice): void
    {
        //
    }
    protected function clearCache()
    {
        Cache::tags('device_categories')->flush();
    }
}
