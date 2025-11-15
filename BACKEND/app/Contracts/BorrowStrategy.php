<?php


namespace App\Contracts;

interface BorrowStrategy
{
    public function validateBorrow(array $data): bool;
    public function calculateDeposit(array $data): float;
    public function processBorrow(array $data): array;
    public function handleReturn(array $data): array;
}
