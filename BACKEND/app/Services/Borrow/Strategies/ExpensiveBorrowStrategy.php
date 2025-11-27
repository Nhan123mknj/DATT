<?php


namespace App\Services\Borrow\Strategies;

use App\Models\Devices;
use App\Models\DeviceUnits;
use App\Services\Borrow\AbstractBorrowStrategy;
use League\Config\Exception\ValidationException;

class ExpensiveBorrowStrategy extends AbstractBorrowStrategy
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



    public function processBorrow(array $data): array
    {
        // Tạo phiếu mượn chờ duyệt
        return [
            'status' => 'pending',
            'message' => 'Yêu cầu mượn đang chờ duyệt. Vui lòng nộp cam kết trách nhiệm.',
            'deposit_required' => false,
            'commitment_required' => true
        ];
    }

    public function handleReturn(array $data): array
    {
        // Kiểm tra tình trạng thiết bị
        $damageCheck = $this->checkDeviceCondition($data['device_unit_id']);

        if (!$damageCheck['passed']) {
            return [
                'status' => 'damaged',
                'message' => 'Thiết bị bị hư hỏng',
                'damage_fee' => $damageCheck['fee']
            ];
        }

        return [
            'status' => 'completed',
            'message' => 'Trả thiết bị thành công'
        ];
    }

    protected function getMinimumDeposit(): float
    {
        return $this->device->price * 0.5;
    }

    protected function getMaxBorrowDuration(): int
    {
        return 30; // 30 ngày
    }

    protected function requiresApproval(): bool
    {
        return true;
    }

    private function checkBorrowHistory(int $userId): bool
    {
        // Implement kiểm tra lịch sử
        return true;
    }

    private function checkDeviceCondition($deviceUnitId): array
    {
        // Implement kiểm tra tình trạng
        return [
            'passed' => true,
            'fee' => 0
        ];
    }

    public function calculateDeposit(array $data): float
    {
        // Không yêu cầu đặt cọc với thiết bị đắt tiền trong trường học
        return 0;
    }
}
