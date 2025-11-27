<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB max
                'dimensions:min_width=100,min_height=100,max_width=4096,max_height=4096',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'Vui lòng chọn ảnh đại diện.',
            'avatar.image' => 'File phải là định dạng ảnh.',
            'avatar.mimes' => 'Ảnh phải có định dạng: jpeg, jpg, png hoặc webp.',
            'avatar.max' => 'Kích thước ảnh không được vượt quá 5MB.',
            'avatar.dimensions' => 'Ảnh phải có kích thước từ 100x100 đến 4096x4096 pixels.',
        ];
    }
}
