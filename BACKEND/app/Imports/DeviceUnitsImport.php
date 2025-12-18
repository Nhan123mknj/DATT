<?php

namespace App\Imports;

use App\Models\DeviceUnits;
use App\Models\Devices;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class DeviceUnitsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Tìm device by name
        $device = Devices::where('name', $row['thiet_bi'])->first();

        if (!$device) {
            Log::warning("Device not found: " . $row['thiet_bi']);
            return null;
        }

        $typeMap = [
            'Thường' => 'normal',
            'Đắt tiền' => 'expensive',
            'Tiêu hao' => 'consumable',
        ];
        $type = $typeMap[$row['loai'] ?? 'Thường'] ?? 'normal';

        $statusMap = [
            'Khả dụng' => 'available',
            'Đang mượn' => 'borrowed',
            'Đã đặt' => 'reserved',
            'Bảo trì' => 'under_maintenance',
            'Hỏng' => 'broken',
        ];
        $status = $statusMap[$row['trang_thai'] ?? 'Khả dụng'] ?? 'available';

        return new DeviceUnits([
            'device_id' => $device->id,
            'serial_number' => $row['serial_number'],
            'type' => $type,
            'status' => $status,
            'purchase_date' => isset($row['ngay_mua']) ? $this->parseDate($row['ngay_mua']) : null,
            'warranty_expiry' => isset($row['bao_hanh_den']) ? $this->parseDate($row['bao_hanh_den']) : null,
            'notes' => $row['ghi_chu'] ?? null,
        ]);
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($date)
    {
        if (empty($date)) return null;

        try {
            // Try d/m/Y format
            $parsed = \DateTime::createFromFormat('d/m/Y', $date);
            if ($parsed) {
                return $parsed->format('Y-m-d');
            }

            // Try Y-m-d format
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'thiet_bi' => 'required|string',
            'serial_number' => 'required|string|unique:device_units,serial_number',
            'loai' => 'required|string',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'thiet_bi.required' => 'Tên thiết bị không được để trống',
            'serial_number.required' => 'Serial number không được để trống',
            'serial_number.unique' => 'Serial number đã tồn tại',
            'loai.required' => 'Loại thiết bị không được để trống',
        ];
    }
}
