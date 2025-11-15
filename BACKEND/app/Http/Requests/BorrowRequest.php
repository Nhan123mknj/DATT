<?php

namespace App\Http\Requests;

use App\Models\Borrows;
use App\Models\DeviceUnits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BorrowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $user = auth('api')->user();
        return $user && $user->role === 'borrower';
    }

    /**
     * Quy tắc validate chính.
     */
    public function rules(): array
    {
        return [
            'expected_return_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:500'],
            'devices' => ['required', 'array', 'min:1,max:5'],
            'devices.*.device_unit_id' => ['required', 'exists:device_units,id', 'distinct'],
            'devices.*.condition_at_borrow' => ['nullable', 'string', 'max:255'],
            'commitment_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'], // file cam kết nếu có
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh.
     */
    public function messages(): array
    {
        return [
            'expected_return_date.required' => 'Ngày dự kiến trả là bắt buộc.',
            'expected_return_date.after' => 'Ngày dự kiến trả phải sau hôm nay.',
            'devices.required' => 'Danh sách thiết bị là bắt buộc.',
            'devices.min' => 'Phải chọn ít nhất 1 thiết bị để mượn.',
            'devices.*.device_unit_id.exists' => 'Thiết bị không tồn tại trong hệ thống.',
            'devices.*.device_unit_id.distinct' => 'Không thể chọn trùng thiết bị.',
            'commitment_file.mimes' => 'File cam kết chỉ chấp nhận định dạng PDF hoặc ảnh.',
            'commitment_file.max' => 'File cam kết không được vượt quá 2MB.',
        ];
    }

    /**
     * Kiểm tra điều kiện nghiệp vụ sau khi validate cơ bản.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $userId = auth('api')->id();

            $activeBorrowsCount = Borrows::where('borrower_id', $userId)
                ->whereIn('status', ['pending'])
                ->count();

            if ($activeBorrowsCount >= 3) {
                $validator->errors()->add(
                    'borrower_id',
                    "Bạn đã có {$activeBorrowsCount} phiếu mượn đang chờ duyệt hoặc đang mượn. Tối đa 3 phiếu. Neu muốn mượn thêm, vui lòng hoàn tất các phiếu hiện tại."
                );
            }

            if ($this->has('devices')) {
                $deviceIds = collect($this->devices)
                    ->pluck('device_unit_id')
                    ->filter();

                if ($deviceIds->isNotEmpty()) {
                    $hasExpensive = DeviceUnits::whereIn('id', $deviceIds)
                        ->whereHas('device', fn($q) => $q->where('category_id', 2))
                        ->exists();

                    if ($hasExpensive && !$this->hasFile('commitment_file')) {
                        $validator->errors()->add('commitment_file', 'Thiết bị đắt tiền yêu cầu nộp file cam kết trách nhiệm.');
                    }
                }
            }
        });
    }
}
