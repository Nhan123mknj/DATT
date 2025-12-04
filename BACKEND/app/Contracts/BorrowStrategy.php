<?php


namespace App\Contracts;

interface BorrowStrategy
{
    public function validateBorrow(array $data): bool;
    public function processBorrow(array $data): array;
    public function handleReturn(array $data): array;
}
