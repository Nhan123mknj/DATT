<?php

namespace App\Exports;

use App\Models\Borrows;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Borrows::with(['borrower', 'details.deviceUnit.device']);

        if (isset($this->filters['status']) && $this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Mã phiếu',
            'Người mượn',
            'Email',
            'Ngày mượn',
            'Hạn trả',
            'Trạng thái',
            'Số thiết bị',
            'Danh sách thiết bị',
            'Ghi chú',
            'Ngày tạo',
        ];
    }

    /**
     * @param mixed $borrow
     * @return array
     */
    public function map($borrow): array
    {
        $statusLabels = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'completed' => 'Đã xuất',
            'returned' => 'Đã trả',
            'overdue' => 'Quá hạn',
            'cancelled' => 'Đã hủy',
        ];


        $deviceList = $borrow->details->map(function ($detail) {
            $deviceName = $detail->deviceUnit->device->name ?? 'N/A';
            $serialNumber = $detail->deviceUnit->serial_number ?? 'N/A';
            return "{$deviceName} (SN: {$serialNumber})";
        })->join('; ');

        return [
            $borrow->id,
            $borrow->borrower->name ?? 'N/A',
            $borrow->borrower->email ?? 'N/A',
            $borrow->borrowed_date ? \Carbon\Carbon::parse($borrow->borrowed_date)->format('d/m/Y') : 'N/A',
            $borrow->expected_return_date ? \Carbon\Carbon::parse($borrow->expected_return_date)->format('d/m/Y') : 'N/A',
            $statusLabels[$borrow->status] ?? $borrow->status,
            $borrow->details->count(),
            $deviceList,
            $borrow->notes ?? '',
            $borrow->created_at->format('d/m/Y H:i'),
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
