<?php

namespace App\Exports;

use App\Models\DeviceUnits;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeviceUnitsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DeviceUnits::with(['device.category'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Thiết bị',
            'Loại',
            'Serial Number',
            'Trạng thái',
            'Ngày mua',
            'Bảo hành đến',
            'Ghi chú',
            'Ngày tạo',
        ];
    }

    /**
     * @param mixed $unit
     * @return array
     */
    public function map($unit): array
    {
        $statusLabels = [
            'available' => 'Khả dụng',
            'borrowed' => 'Đang mượn',
            'reserved' => 'Đã đặt',
            'under_maintenance' => 'Bảo trì',
            'broken' => 'Hỏng',
        ];

        return [
            $unit->id,
            $unit->device->name ?? 'N/A',
            $unit->device->category->name ?? 'N/A',
            $unit->serial_number,
            $statusLabels[$unit->status] ?? $unit->status,
            $unit->purchase_date ? date('d/m/Y', strtotime($unit->purchase_date)) : '',
            $unit->warranty_expiry ? date('d/m/Y', strtotime($unit->warranty_expiry)) : '',
            $unit->notes ?? '',
            $unit->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
