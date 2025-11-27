<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Clear existing menu items
            MenuItem::query()->delete();

            // Dashboard
            MenuItem::create([
                'parent_id' => null,
                'label' => 'Trang chủ',
                'url' => '/dashboard',
                'icon' => 'chart-line',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            // Device Management with children
            $deviceMenu = MenuItem::create([
                'parent_id' => null,
                'label' => 'Quản lý Thiết bị',
                'url' => null,
                'icon' => 'boxes',
                'sort_order' => 2,
                'is_active' => true,
            ]);

            MenuItem::create([
                'parent_id' => $deviceMenu->id,
                'label' => 'Danh mục Thiết bị',
                'url' => '/devices/categories',
                'icon' => 'list',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            MenuItem::create([
                'parent_id' => $deviceMenu->id,
                'label' => 'Danh sách Thiết bị',
                'url' => '/devices',
                'icon' => 'microchip',
                'sort_order' => 2,
                'is_active' => true,
            ]);

            MenuItem::create([
                'parent_id' => $deviceMenu->id,
                'label' => 'Đơn vị Thiết bị',
                'url' => '/device-units',
                'icon' => 'cube',
                'sort_order' => 3,
                'is_active' => true,
            ]);

            // Borrow Management with children
            $borrowMenu = MenuItem::create([
                'parent_id' => null,
                'label' => 'Quản lý Mượn/Trả',
                'url' => null,
                'icon' => 'exchange-alt',
                'sort_order' => 3,
                'is_active' => true,
            ]);

            MenuItem::create([
                'parent_id' => $borrowMenu->id,
                'label' => 'Phiếu Mượn',
                'url' => '/borrows',
                'icon' => 'file-alt',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            MenuItem::create([
                'parent_id' => $borrowMenu->id,
                'label' => 'Đặt trước Thiết bị',
                'url' => '/reservations',
                'icon' => 'calendar-alt',
                'sort_order' => 2,
                'is_active' => true,
            ]);

            // Users Management
            MenuItem::create([
                'parent_id' => null,
                'label' => 'Quản lý Người dùng',
                'url' => '/users',
                'icon' => 'users',
                'sort_order' => 4,
                'is_active' => true,
            ]);

            // Settings
            MenuItem::create([
                'parent_id' => null,
                'label' => 'Cài đặt',
                'url' => '/settings',
                'icon' => 'cog',
                'sort_order' => 5,
                'is_active' => true,
            ]);
        });
    }
}
