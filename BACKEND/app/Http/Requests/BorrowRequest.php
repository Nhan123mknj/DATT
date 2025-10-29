<?php

namespace App\Http\Requests;

use App\Models\Borrows;
use Illuminate\Foundation\Http\FormRequest;

class BorrowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expected_return_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
            'devices' => 'required|array|min:1',
            'devices.*.device_unit_id' => 'required|exists:device_units,id|distinct',
            'devices.*.condition_at_borrow' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'expected_return_date.required' => 'Ngày dự kiến trả là bắt buộc.',
            'expected_return_date.after' => 'Ngày dự kiến trả phải sau hôm nay.',
            'devices.required' => 'Danh sách thiết bị là bắt buộc.',
            'devices.min' => 'Phải chọn ít nhất 1 thiết bị để mượn.',
            'devices.*.device_unit_id.exists' => 'Thiết bị không tồn tại.',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $userId = auth('api')->user()->id;
            if (Borrows::where('borrower_id', $userId)->where('status', 'borrowed')->exists()) {
                $validator->errors()->add('borrower_id', 'Bạn đang có phiếu mượn chưa trả. Không thể tạo thêm.');
            }
        });
    }
}
