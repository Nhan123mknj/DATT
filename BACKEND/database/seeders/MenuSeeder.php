<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $sidebar = Menu::updateOrCreate(
                ['key' => 'sidebar'],
                ['name' => 'Sidebar', 'is_active' => true]
            );

            $items = [
                [
                    'label' => 'Dashboard',
                    'route' => '/dashboard',
                    'icon' => 'mdi-view-dashboard',
                    'children' => [],
                    'roles' => ['admin', 'staff', 'borrower'],
                ],
                [
                    'label' => 'Users',
                    'route' => '/admin/users',
                    'icon' => 'mdi-account',
                    'children' => [],
                    'roles' => ['admin'],
                ],
                [
                    'label' => 'Devices',
                    'route' => '/admin/devices',
                    'icon' => 'mdi-usb',
                    'children' => [
                        [
                            'label' => 'Categories',
                            'route' => '/admin/device-categories',
                            'icon' => 'mdi-shape',
                            'roles' => ['admin', 'staff'],
                        ],
                        [
                            'label' => 'Units',
                            'route' => '/admin/device-units',
                            'icon' => 'mdi-cube',
                            'roles' => ['admin', 'staff'],
                        ],
                    ],
                    'roles' => ['admin', 'staff'],
                ],
                [
                    'label' => 'Borrow',
                    'route' => '/borrow',
                    'icon' => 'mdi-clipboard',
                    'children' => [],
                    'roles' => ['borrower', 'admin'],
                ],
            ];

            $order = 0;
            foreach ($items as $itemData) {
                $item = MenuItem::updateOrCreate(
                    ['menu_id' => $sidebar->id, 'label' => $itemData['label']],
                    [
                        'menu_id' => $sidebar->id,
                        'parent_id' => null,
                        'label' => $itemData['label'],
                        'route' => $itemData['route'],
                        'icon' => $itemData['icon'] ?? null,
                        'display_order' => $order++,
                        'is_active' => true,
                    ]
                );
                MenuItemRole::where('menu_item_id', $item->id)->delete();
                foreach ($itemData['roles'] as $role) {
                    MenuItemRole::create(['menu_item_id' => $item->id, 'role' => $role]);
                }

                $childOrder = 0;
                foreach ($itemData['children'] as $child) {
                    $childItem = MenuItem::updateOrCreate(
                        ['menu_id' => $sidebar->id, 'parent_id' => $item->id, 'label' => $child['label']],
                        [
                            'menu_id' => $sidebar->id,
                            'parent_id' => $item->id,
                            'label' => $child['label'],
                            'route' => $child['route'],
                            'icon' => $child['icon'] ?? null,
                            'display_order' => $childOrder++,
                            'is_active' => true,
                        ]
                    );
                    MenuItemRole::where('menu_item_id', $childItem->id)->delete();
                    foreach ($child['roles'] as $role) {
                        MenuItemRole::create(['menu_item_id' => $childItem->id, 'role' => $role]);
                    }
                }
            }
        });
    }
}
