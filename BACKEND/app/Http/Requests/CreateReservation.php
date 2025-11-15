<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateReservation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth("api")->user()->role() === 'borrower';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reserved_from' => [
                'required',
                'date',
                'after:now',
            ],
            'reserved_until' => [
                'required',
                'date',
                'after:reserved_from',
            ],
            'devices' => [
                'required',
                'array',
                'min:1',
            ],
            'devices.*.device_unit_id' => [
                'required',
                'integer',
                'exists:device_units,id',
                Rule::unique('device_reservation_details', 'device_unit_id')
                    ->where(function ($query) {
                        $query->whereRaw('? BETWEEN reserved_from AND reserved_until', [$this->reserved_from])
                            ->orWhereRaw('? BETWEEN reserved_from AND reserved_until', [$this->reserved_until]);
                    }),
            ],
            'notes' => 'nullable|string|max:1000',
            'commitment_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'reserved_from.after' => 'Thời gian bắt đầu phải sau thời gian hiện tại.',
            'reserved_until.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'devices.min' => 'Phải chọn ít nhất 1 thiết bị.',
            'devices.*.device_unit_id.exists' => 'Thiết bị không tồn tại.',
            'devices.*.device_unit_id.unique' => 'Thiết bị đã được đặt trong khung giờ này.',
            'commitment_file.mimes' => 'File cam kết chỉ chấp nhận định dạng PDF hoặc ảnh.',
            'commitment_file.max' => 'File cam kết không được vượt quá 2MB.',
        ];
    }
}
