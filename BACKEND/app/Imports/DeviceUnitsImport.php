<?php

namespace App\Imports;

use App\Models\DeviceUnits;
use App\Models\Devices;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DeviceUnitsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithChunkReading,
    WithBatchInserts,
    ShouldQueue
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
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
            'Bảo trì' => 'maintenance',
            'Đã thanh lý' => 'retired',
        ];
        $status = $statusMap[$row['trang_thai'] ?? 'Khả dụng'] ?? 'available';

        return new DeviceUnits([
            'device_id' => $device->id,
            'serial_number' => $row['serial_number'],
            'status' => $status,
            'purchase_date' => isset($row['ngay_mua']) ? $this->parseDate($row['ngay_mua']) : null,
            'warranty_end' => isset($row['bao_hanh_den']) ? $this->parseDate($row['bao_hanh_den']) : null,
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
        ];
    }

    /**
     * Chunk size for reading
     * 
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000; // Process 1000 rows at a time
    }

    /**
     * Batch size for inserts
     * 
     * @return int
     */
    public function batchSize(): int
    {
        return 500; // Insert 500 rows at a time
    }
}
