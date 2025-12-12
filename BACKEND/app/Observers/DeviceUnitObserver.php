<?php

namespace App\Observers;

use App\Models\DeviceUnits;
use App\Models\Devices;

class DeviceUnitObserver
{
    /**
     * Handle the DeviceUnits "created" event.
     */
    public function created(DeviceUnits $deviceUnit): void
    {
        \Illuminate\Support\Facades\Log::info('DeviceUnit created: ' . $deviceUnit->id);
        $this->updateTotalUnits($deviceUnit->device_id);
    }

    /**
     * Handle the DeviceUnits "updated" event.
     */
    public function updated(DeviceUnits $deviceUnit): void
    {
        // If device_id changed (moved to another device type), update both
        if ($deviceUnit->isDirty('device_id')) {
            $this->updateTotalUnits($deviceUnit->getOriginal('device_id'));
            $this->updateTotalUnits($deviceUnit->device_id);
        }

        // If status changed to/from 'disposed' or similar non-countable status, 
        // we might want to update. But for now, total_units usually means 
        // total physical units regardless of status, or total active?
        // Let's stick to simple count of records for now as per "total_units" convention.
        // If soft deletes are used, deleted() handles it.
    }

    /**
     * Handle the DeviceUnits "deleted" event.
     */
    public function deleted(DeviceUnits $deviceUnit): void
    {
        $this->updateTotalUnits($deviceUnit->device_id);
    }

    /**
     * Handle the DeviceUnits "restored" event.
     */
    public function restored(DeviceUnits $deviceUnit): void
    {
        $this->updateTotalUnits($deviceUnit->device_id);
    }

    /**
     * Handle the DeviceUnits "force deleted" event.
     */
    public function forceDeleted(DeviceUnits $deviceUnit): void
    {
        $this->updateTotalUnits($deviceUnit->device_id);
    }

    /**
     * Update the total_units count for a device.
     */
    private function updateTotalUnits($deviceId)
    {
        if (!$deviceId) return;

        $count = DeviceUnits::where('device_id', $deviceId)->count();

        Devices::where('id', $deviceId)->update(['total_units' => $count]);
    }
}
