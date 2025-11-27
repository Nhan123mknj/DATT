<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get all root menu items
     */
    public function index()
    {
        $items = MenuItem::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $items,
        ]);
    }

    /**
     * Get menu item with nested children
     */
    public function show($id)
    {
        $item = MenuItem::with('childrenRecursive')->find($id);

        if (!$item) {
            return response()->json(['message' => 'Menu item không tồn tại'], 404);
        }

        return response()->json([
            'data' => $item,
        ]);
    }

    /**
     * Create new menu item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'label' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:100',
            'badge_color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'description' => 'nullable|string',
        ]);

        $item = MenuItem::create($validated);

        return response()->json([
            'message' => 'Tạo menu item thành công',
            'data' => $item,
        ], 201);
    }

    /**
     * Update menu item
     */
    public function update(Request $request, $id)
    {
        $item = MenuItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Menu item không tồn tại'], 404);
        }

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'label' => 'string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:100',
            'badge_color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'description' => 'nullable|string',
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Cập nhật menu item thành công',
            'data' => $item,
        ]);
    }

    /**
     * Delete menu item
     */
    public function destroy($id)
    {
        $item = MenuItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Menu item không tồn tại'], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Xóa menu item thành công',
        ]);
    }

    /**
     * Reorder menu items
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            MenuItem::where('id', $itemData['id'])->update(['sort_order' => $itemData['sort_order']]);
        }

        return response()->json([
            'message' => 'Cập nhật thứ tự menu thành công',
        ]);
    }

    /**
     * Get all menu items (for frontend)
     */
    public function getBySlug($slug = null)
    {
        // Get ALL items (root + children) for building tree
        $items = MenuItem::active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $items,
        ]);
    }
}
