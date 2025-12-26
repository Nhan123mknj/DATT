<?php

namespace App\Exports;

use App\Models\DeviceReservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReservationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = DeviceReservation::with(['user', 'details.deviceUnit.device']);

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
            'Người đặt',
            'Email',
            'Từ ngày',
            'Đến ngày',
            'Trạng thái',
            'Số thiết bị',
            'Danh sách thiết bị',
            'Ghi chú',
            'Ngày tạo',
        ];
    }

    /**
     * @param mixed $reservation
     * @return array
     */
    public function map($reservation): array
    {
        $statusLabels = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        $deviceList = $reservation->details->map(function ($detail) {
            $deviceName = $detail->deviceUnit->device->name ?? 'N/A';
            $serialNumber = $detail->deviceUnit->serial_number ?? 'N/A';
            return "{$deviceName} (SN: {$serialNumber})";
        })->join('; ');

        return [
            $reservation->id,
            $reservation->user->name ?? 'N/A',
            $reservation->user->email ?? 'N/A',
            $reservation->reserved_from ? \Carbon\Carbon::parse($reservation->reserved_from)->format('d/m/Y') : 'N/A',
            $reservation->reserved_until ? \Carbon\Carbon::parse($reservation->reserved_until)->format('d/m/Y') : 'N/A',
            $statusLabels[$reservation->status] ?? $reservation->status,
            $reservation->details->count(),
            $deviceList,
            $reservation->notes ?? '',
            $reservation->created_at->format('d/m/Y H:i'),
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
