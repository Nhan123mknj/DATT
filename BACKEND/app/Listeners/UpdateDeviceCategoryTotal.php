<?php

namespace App\Listeners;

use App\Events\AddNewDevice;
use App\Events\RemoveDevice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDeviceCategoryTotal
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($event instanceof AddNewDevice && $event->device->category) {
            $event->device->category->increment('total_devices');
        } elseif ($event instanceof RemoveDevice && $event->device->category) {
            $event->device->category->decrement('total_devices');
        }
    }
}
