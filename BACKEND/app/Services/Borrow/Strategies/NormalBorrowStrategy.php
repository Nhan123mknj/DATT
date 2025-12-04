<?php


namespace App\Services\Borrow\Strategies;

use App\Models\Devices;
use App\Models\DeviceUnits;
use Illuminate\Validation\ValidationException;
use App\Services\Borrow\AbstractBorrowStrategy;

class NormalBorrowStrategy extends AbstractBorrowStrategy
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
        return $this->device->price * 0.2 * $data['quantity']; 
    }

    public function processBorrow(array $data): array
    {
        $requiresApproval = $data['quantity'] > 3;

        return [
            'status' => $requiresApproval ? 'pending' : 'borrowed',
            'message' => $requiresApproval ? 'Yêu cầu mượn đang chờ duyệt' : 'Mượn thành công',

        ];
    }

    public function handleReturn(array $data): array
    {
        return [
            'status' => 'completed',
            'message' => 'Trả thiết bị thành công'
        ];
    }



    protected function getMaxBorrowDuration(): int
    {
        return 14;
    }

    protected function requiresApproval(): bool
    {
        return false;
    }
}
