<?php


namespace App\Services\Borrow;

use App\Contracts\BorrowStrategy;
use App\Models\Devices;
use App\Models\DeviceUnits;

abstract class AbstractBorrowStrategy implements BorrowStrategy
{
    protected $device;

    public function __construct(Devices $device)
    {
        $this->device = $device;
    }
    abstract protected function getMaxBorrowDuration(): int;
    abstract protected function requiresApproval(): bool;
}
