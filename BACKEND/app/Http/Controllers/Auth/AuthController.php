<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\MediaService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected UserService $userService;
    protected MediaService $mediaService;

    public function __construct(AuthService $authService, UserService $userService, MediaService $mediaService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->mediaService = $mediaService;
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(LoginRequest $request)
    {

        $result = $this->authService->login($request->validated());

        return response()->json(
            $result,
            $result['code'] ?? 200
        );
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    public function register(Request $request)
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, $result['status']);
    }

    public function refresh()
    {
        return response()->json($this->authService->refresh());
    }

    public function userProfile()
    {
        return response()->json([
            'status' => true,
            'data' =>  auth('api')->user()->load('avatar'),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $result = $this->authService->changePassword($request->validated());
        return response()->json($result, $result['status'] ?? 200);
    }

    public function updateProfile(UpdateUserRequest $request)
    {
        $result = $this->authService->updateProfile($request->validated());
        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * Upload avatar for current user
     */
    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $user = auth('api')->user();
        $file = $request->file('avatar');

        /** @var \App\Models\User $user */
        $media = $this->mediaService->attachMedia($user, $file, 'avatar');

        if (!$media) {
            return response()->json([
                'message' => 'Upload thất bại'
            ], 500);
        }

        return response()->json([
            'message' => 'Upload avatar thành công',
            'avatar' => [
                'id' => $media->id,
                'url' => $media->url,
                'thumbnail' => $media->thumbnail,
            ],
        ]);
    }
    public function deleteAvatar()
    {
        $user = auth('api')->user();
        /** @var \App\Models\User $user */
        $this->mediaService->deleteMediaByType($user, 'avatar');

        return response()->json(['message' => 'Đã xóa avatar']);
    }
    public function profile()
    {
        $user = auth('api')->user()->load('avatar');

        return response()->json([
            'user' => $user,
            'avatar_url' => $user->avatar_url,
        ]);
    }
}
