<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeviceUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy id các thiết bị mẫu
        $normalId = \App\Models\Devices::where('category_id', 1)->first()?->id;
        $expensiveId = \App\Models\Devices::where('category_id', 2)->first()?->id;
        $consumableId = \App\Models\Devices::where('category_id', 3)->first()?->id;

        $units = [];
        if ($normalId) {
            $units[] = [
                'device_id' => $normalId,
                'serial_number' => 'SN-NORMAL-001',
                'status' => 'available',
                'purchase_date' => now()->subYear(),
                'warranty_end' => now()->addYear(),
                'notes' => 'Thiết bị thường mẫu',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $units[] = [
                'device_id' => $normalId,
                'serial_number' => 'SN-NORMAL-002',
                'status' => 'available',
                'purchase_date' => now()->subMonths(6),
                'warranty_end' => now()->addMonths(18),
                'notes' => 'Thiết bị thường mẫu',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($expensiveId) {
            $units[] = [
                'device_id' => $expensiveId,
                'serial_number' => 'SN-EXP-001',
                'status' => 'available',
                'purchase_date' => now()->subYear(),
                'warranty_end' => now()->addYear(),
                'notes' => 'Thiết bị đắt tiền mẫu',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $units[] = [
                'device_id' => $expensiveId,
                'serial_number' => 'SN-EXP-002',
                'status' => 'available',
                'purchase_date' => now()->subMonths(8),
                'warranty_end' => now()->addMonths(16),
                'notes' => 'Thiết bị đắt tiền mẫu',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($consumableId) {
            $units[] = [
                'device_id' => $consumableId,
                'serial_number' => 'SN-CONSUM-001',
                'status' => 'available',
                'purchase_date' => now()->subMonths(2),
                'warranty_end' => now()->addMonths(10),
                'notes' => 'Thiết bị tiêu hao mẫu',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $units[] = [
                'device_id' => $consumableId,
                'serial_number' => 'SN-CONSUM-002',
                'status' => 'available',
                'purchase_date' => now()->subMonths(1),
                'warranty_end' => now()->addMonths(11),
                'notes' => 'Thiết bị tiêu hao mẫu',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($units)) {
            \App\Models\DeviceUnits::insert($units);
        }
    }
}
