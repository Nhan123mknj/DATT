<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
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
        $maxFiles = $this->input('max_files', 10);
        $maxFileSize = $this->input('max_file_size', 10240); // Default 10MB in KB

        return [
            'files' => [
                'required',
                'array',
                "max:{$maxFiles}",
            ],
            'files.*' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                "max:{$maxFileSize}",
            ],
            'type' => [
                'sometimes',
                'string',
                'max:50',
            ],
            'folder' => [
                'sometimes',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'files.required' => 'Vui lòng chọn ít nhất một file.',
            'files.array' => 'Files phải là mảng.',
            'files.max' => 'Không được upload quá :max file cùng lúc.',
            'files.*.required' => 'File không được để trống.',
            'files.*.image' => 'File phải là định dạng ảnh.',
            'files.*.mimes' => 'Ảnh phải có định dạng: jpeg, jpg, png, webp hoặc gif.',
            'files.*.max' => 'Kích thước file không được vượt quá :max KB.',
        ];
    }
}
