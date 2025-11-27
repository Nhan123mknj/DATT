<?php


namespace App\Services\Borrow\Strategies;

use App\Models\Devices;
use App\Models\DeviceUnits;
use App\Services\Borrow\AbstractBorrowStrategy;
use League\Config\Exception\ValidationException;

class ConsumableBorrowStrategy extends AbstractBorrowStrategy
{
    public function validateBorrow(array $data): bool
    {
        $deviceId = $data['device_id'];
        $quantity = $data['quantity'];
        $duration = $data['duration'];

        $availableCount = DeviceUnits::where('device_id', $deviceId)
            ->where('status', 'available')
            ->count();

        if ($quantity > $availableCount) {
            $device = Devices::find($deviceId);
            $deviceName = $device?->name;

            throw ValidationException::withMessages([
                'devices' => "Thiết bị '{$deviceName}' chỉ còn {$availableCount} cái khả dụng, không đủ {$quantity}."
            ]);
        }


        $maxDays = $this->getMaxBorrowDuration();
        if ($duration > $maxDays) {
            throw ValidationException::withMessages([
                'expected_return_date' => "Thời gian mượn không được vượt quá {$maxDays} ngày."
            ]);
        }

        return true;
    }

    public function calculateDeposit(array $data): float
    {
        // Thiết bị tiêu hao không cần đặt cọc
        return 0;
    }

    public function processBorrow(array $data): array
    {
        // Giảm số lượng tồn kho
        $this->device->decrement('quantity', $data['quantity']);

        return [
            'status' => 'approved',
            'message' => 'Mượn thành công',
            'deposit_required' => false
        ];
    }

    public function handleReturn(array $data): array
    {
        return [
            'status' => 'completed',
            'message' => 'Thiết bị tiêu hao không cần trả lại'
        ];
    }

    protected function getMinimumDeposit(): float
    {
        return 0;
    }

    protected function getMaxBorrowDuration(): int
    {
        return 0; // Không giới hạn thời gian
    }

    protected function requiresApproval(): bool
    {
        return false;
    }
}
