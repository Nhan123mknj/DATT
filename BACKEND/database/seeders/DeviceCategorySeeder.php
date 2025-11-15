<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('device_categories')->insert([
            [
                'name' => 'Thiết bị tiêu hao',
                'code' => 'CONSUMABLE',
                'type' => 'consumable',
                'deposit_rate' => 0,
                'max_borrow_duration' => 0,
                'requires_approval' => false,
                'description' => 'Các thiết bị sử dụng một lần như hóa chất, văn phòng phẩm',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Thiết bị giá trị cao',
                'code' => 'EXPENSIVE',
                'type' => 'expensive',
                'deposit_rate' => 50, // 50%
                'max_borrow_duration' => 30,
                'requires_approval' => true,
                'description' => 'Các thiết bị có giá trị cao như máy phân tích, kính hiển vi điện tử',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Thiết bị thông thường',
                'code' => 'NORMAL',
                'type' => 'normal',
                'deposit_rate' => 0,
                'max_borrow_duration' => 14,
                'requires_approval' => true,
                'description' => 'Các thiết bị thông dụng như máy tính, màn hình',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
