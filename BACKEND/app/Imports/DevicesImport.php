<?php

namespace App\Imports;

use App\Models\Devices;
use App\Models\CategoriesDevice;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class DevicesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $category = CategoriesDevice::where('name', $row['danh_muc'])->first();

        if (!$category) {
            Log::warning("Category not found: " . $row['danh_muc']);
            return null;
        }

        return new Devices([
            'name' => $row['ten_thiet_bi'],
            'category_id' => $category->id,
            'manufacturer' => $row['nha_san_xuat'],
            'model' => $row['model'],
            'specifications' => isset($row['thong_so_ky_thuat']) ? json_decode($row['thong_so_ky_thuat'], true) : null,
            'is_active' => ($row['trang_thai'] ?? 'Kích hoạt') === 'Kích hoạt' ? 1 : 0,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'ten_thiet_bi' => 'required|string|max:255',
            'danh_muc' => 'required|string',
            'nha_san_xuat' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'ten_thiet_bi.required' => 'Tên thiết bị không được để trống',
            'danh_muc.required' => 'Danh mục không được để trống',
            'nha_san_xuat.required' => 'Nhà sản xuất không được để trống',
            'model.required' => 'Model không được để trống',
        ];
    }
}
