<?php


namespace App\Services\Borrow;

use App\Models\Devices;
use App\Services\Borrow\Strategies\ConsumableBorrowStrategy;
use App\Services\Borrow\Strategies\ExpensiveBorrowStrategy;
use App\Services\Borrow\Strategies\NormalBorrowStrategy;

class BorrowStrategyFactory
{
    public static function createStrategy(Devices $device): AbstractBorrowStrategy
    {
        return match ($device->category_id) {
            3 => new ConsumableBorrowStrategy($device),
            2 => new ExpensiveBorrowStrategy($device),
            default => new NormalBorrowStrategy($device),
        };
    }
}
