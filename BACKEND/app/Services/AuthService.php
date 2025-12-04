<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthService
{

    public function login(array $data)
    {
        if (! $token = auth('api')->attempt($data)) {
            return [
                'status' => false,
                'message' => 'Sai email hoặc mật khẩu.',
                'code' => 401
            ];
        }

        return [
            'status' => true,
            'message' => 'Đăng nhập thành công.',
            'data' => $this->createNewToken($token)
        ];
    }

    public function register(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors(), 'status' => 400];
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        return ['message' => 'Đã đăng ký thành công', 'user' => $user, 'status' => 201];
    }
    public function logout()
    {
        auth('api')->logout();
        return ['message' => 'User successfully signed out'];
    }

    public function refresh()
    {
        return $this->createNewToken(auth('api')->refresh());
    }

    public function changePassword(array $data)
    {
        $user = auth('api')->user();

        if (!Hash::check($data['old_password'], $user->password)) {
            return ['error' => 'Old password is incorrect', 'status' => 400];
        }

        $user->update(['password' => bcrypt($data['new_password'])]);

        return ['message' => 'Password changed successfully', 'user' => $user];
    }

    public function updateProfile(array $data)
    {
        $user = auth('api')->user();
        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        return ['message' => 'Profile updated successfully', 'user' => $user];
    }

    protected function createNewToken($token)
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => auth('api')->user()
        ];
    }
}
