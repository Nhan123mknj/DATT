<?php

namespace App\Observers;

use App\Models\BorrowsDetail;
use App\Models\DeviceUnits;

class BorrowsDetailObserver
{
    /**
     * Handle the BorrowsDetail "created" event.
     */
    public function created(BorrowsDetail $borrowsDetail): void
    {
        DeviceUnits::where('id', $borrowsDetail->device_unit_id)
            ->update(['status' => 'available']);
    }

    /**
     * Handle the BorrowsDetail "updated" event.
     */
    public function updated(BorrowsDetail $borrowsDetail): void
    {
        //
    }

    /**
     * Handle the BorrowsDetail "deleted" event.
     */
    public function deleted(BorrowsDetail $borrowsDetail): void
    {
        //
    }

    /**
     * Handle the BorrowsDetail "restored" event.
     */
    public function restored(BorrowsDetail $borrowsDetail): void
    {
        //
    }

    /**
     * Handle the BorrowsDetail "force deleted" event.
     */
    public function forceDeleted(BorrowsDetail $borrowsDetail): void
    {
        //
    }
}
