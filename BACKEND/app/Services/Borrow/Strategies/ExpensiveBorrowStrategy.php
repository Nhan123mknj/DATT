<?php


namespace App\Services\Borrow\Strategies;

use App\Services\Borrow\AbstractBorrowStrategy;

class ExpensiveBorrowStrategy extends AbstractBorrowStrategy
{
    public function validateBorrow(array $data): bool
    {
        if ($this->device->quantity < $data['quantity']) {
            throw new \Exception('Số lượng yêu cầu vượt quá số lượng tồn kho');
        }

        if ($data['duration'] > $this->getMaxBorrowDuration()) {
            throw new \Exception('Thời gian mượn vượt quá quy định');
        }

        // Kiểm tra lịch sử mượn trả
        if (!$this->checkBorrowHistory($data['user_id'])) {
            throw new \Exception('Người dùng có lịch sử mượn trả không tốt');
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
