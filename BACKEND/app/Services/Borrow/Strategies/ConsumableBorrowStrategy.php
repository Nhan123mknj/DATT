<?php


namespace App\Services\Borrow\Strategies;

use App\Services\Borrow\AbstractBorrowStrategy;

class ConsumableBorrowStrategy extends AbstractBorrowStrategy
{
    public function validateBorrow(array $data): bool
    {
        if ($this->device->quantity < $data['quantity']) {
            throw new \Exception('Số lượng yêu cầu vượt quá số lượng tồn kho');
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
