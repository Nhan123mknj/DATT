<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeviceCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceCategoriesController extends Controller
{
    protected DeviceCategoryService $deviceCategoryService;

    public function __construct(DeviceCategoryService $deviceCategoryService)
    {
        $this->deviceCategoryService = $deviceCategoryService;
    }
    /**
     * 
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'with_devices']);
        $perPage = $request->get('page', 15);

        $categories = $this->deviceCategoryService->listCategories($filters, $perPage);
        if ($categories->isEmpty()) {
            return response()->json(['message' => 'No categories found'], 404);
        }
        return response()->json($categories, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:device_categories,name',
            'code' => 'required|string|max:100|unique:device_categories,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $category = $this->deviceCategoryService->createCategory($request->all());
            return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = $this->deviceCategoryService->getCategoryById($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json([
            'name' => $category->name,
            'devices' => $category->devices
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:device_categories,name,' . $id,
            'code' => 'sometimes|required|string|max:100|unique:device_categories,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $category = $this->deviceCategoryService->updateCategory($id, $request->all());
            return response()->json(['message' => 'Category updated successfully', 'category' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->deviceCategoryService->deleteCategory($id);
            return response()->json(['message' => 'Category and associated devices deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete category: ' . $e->getMessage()], 500);
        }
    }

    public function previewDelete($id)
    {
        $preview = $this->deviceCategoryService->previewDelete($id);
        return response()->json($preview, 200);
    }

    public function edit(string $id)
    {
        $category = $this->deviceCategoryService->getCategoryById($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'category' => $category,
        ], 200);
    }
}
