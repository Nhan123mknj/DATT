<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function showByKey(Request $request)
    {
        $user = $request->user();
        $role = $user?->role ?? 'borrower';

        $rootItems = MenuItem::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with([
                'children' => function ($q) {
                    $q->where('is_active', true)->with('roles');
                },
                'roles'
            ])
            ->orderBy('display_order')
            ->get();

        // Lọc theo role
        $filterByRole = function ($item) use (&$filterByRole, $role) {
            $visible = $item->roles->contains('role', $role);
            $children = $item->children->map($filterByRole)->filter();
            $item->setRelation('children', $children->values());
            return $visible || $children->isNotEmpty() ? $item : null;
        };

        $items = $rootItems->map($filterByRole)->filter()->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
