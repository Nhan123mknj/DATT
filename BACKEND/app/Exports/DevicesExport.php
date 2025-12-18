<?php

namespace App\Exports;

use App\Models\Devices;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DevicesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Devices::with(['category'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tên thiết bị',
            'Danh mục',
            'Nhà sản xuất',
            'Model',
            'Thông số kỹ thuật',
            'Tổng số lượng',
            'Trạng thái',
            'Ngày tạo',
        ];
    }

    /**
     * @param mixed $device
     * @return array
     */
    public function map($device): array
    {
        return [
            $device->id,
            $device->name,
            $device->category->name ?? 'N/A',
            $device->manufacturer,
            $device->model,
            is_array($device->specifications) ? json_encode($device->specifications) : $device->specifications,
            $device->total_units ?? 0,
            $device->is_active ? 'Kích hoạt' : 'Tạm dừng',
            $device->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
