<?php

namespace App\Observers;

use App\Models\DeviceLog;
use App\Models\DeviceUnits;

class DeviceUnitObserver
{
    /**
     * Handle the DeviceUnits "updated" event.
     */
    public function updated(DeviceUnits $deviceUnit): void
    {
        // if ($deviceUnit->isDirty('status')) {
        //     DeviceLog::create([
        //         'device_unit_id' => $deviceUnit->id,
        //         'action' => 'status_change',
        //         'actor_id' => auth()->id() ?? 1, // Default to system/admin if no auth
        //         'previous_status' => $deviceUnit->getOriginal('status'),
        //         'new_status' => $deviceUnit->status,
        //         'notes' => 'Status changed automatically',
        //     ]);
        // }
    }
}
