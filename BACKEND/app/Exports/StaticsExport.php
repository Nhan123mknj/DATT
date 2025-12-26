<?php

namespace App\Exports;

use App\Models\Borrows;
use App\Models\BorrowsDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaticsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $borrowDetails = BorrowsDetail::with([
            'borrow.borrower',
            'deviceUnit.device'
        ])
            ->whereNull('returned_at')
            ->get()
            ->groupBy('borrow.borrower_id');

        $borrowed = $borrowDetails->map(function ($details, $borrowerId) {
            $borrower = $details->first()->borrow->borrower;

            $uniqueBorrows = $details->pluck('borrow_id')->unique();

            $devices = $details->map(function ($detail) {
                return [
                    'device_name' => $detail->deviceUnit->device->name ?? 'N/A',
                    'serial_number' => $detail->deviceUnit->serial_number ?? 'N/A',
                    'borrowed_date' => $detail->borrow->borrowed_date ?? null,
                ];
            });

            return [
                'borrower_name' => $borrower->name ?? 'N/A',
                'borrower_code' => $borrower->code ?? 'N/A',
                'borrower_email' => $borrower->email ?? 'N/A',
                'borrow_count' => $uniqueBorrows->count(),
                'total_devices' => $devices->count(),
                'devices' => $devices,
            ];
        })
            ->values();

        return $borrowed;
    }

    public function headings(): array
    {
        return [
            'Tên người mượn',
            'Mã người mượn',
            'Email',
            'Số lần mượn',
            'Tổng số thiết bị',
            'Chi tiết thiết bị',
        ];
    }

    public function map($static): array
    {

        $devicesList = $static['devices']->map(function ($device) {
            return sprintf(
                "%s (SN: %s)",
                $device['device_name'],
                $device['serial_number']
            );
        })->implode('; ');

        return [
            $static['borrower_name'],
            $static['borrower_code'],
            $static['borrower_email'],
            $static['borrow_count'],
            $static['total_devices'],
            $devicesList,
        ];
    }
}
