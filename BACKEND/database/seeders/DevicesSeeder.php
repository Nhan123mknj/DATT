<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed 3 loại thiết bị mẫu
        $devices = [
            [
                'name' => 'Laptop Dell XPS 13',
                'model' => 'XPS13-2022',
                'category_id' => 1,
                'manufacturer' => 'Dell',
                'price' => 20000000,
                'specifications' => json_encode(['CPU' => 'Intel i7', 'RAM' => '16GB', 'Storage' => '512GB SSD']),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Máy phân tích phổ',
                'model' => 'SPEC-5000',
                'category_id' => 2,
                'manufacturer' => 'Agilent',
                'price' => 150000000,
                'specifications' => json_encode(['Type' => 'Phân tích phổ', 'Công suất' => '5000W']),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hộp khẩu trang y tế',
                'model' => 'MASK-2025',
                'category_id' => 3,
                'manufacturer' => 'VinMask',
                'price' => 50000,
                'specifications' => json_encode(['Loại' => 'Khẩu trang', 'Số lượng' => '50 cái/hộp']),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        \App\Models\Devices::insert($devices);
    }
}
