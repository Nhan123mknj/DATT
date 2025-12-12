<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth('api')->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:student,teacher,staff,admin,borrower',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required'  => 'Tên tài khoản không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email'    => 'Email không hợp lệ.',
            'email.unique'   => 'Email đã được sử dụng.',
            'role.required'  => 'Vui lòng chọn vai trò.',
            'role.in'        => 'Giá trị vai trò không hợp lệ.',
        ];
    }
}
