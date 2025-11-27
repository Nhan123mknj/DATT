<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected UserService $userService;

    public function __construct(AuthService $authService, UserService $userService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
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
            'data' => auth('api')->user(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $result = $this->authService->changePassword($request->validated());
        return response()->json($result, $result['status'] ?? 200);
    }

    public function updateProfile(Request $request)
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
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
        if (!$request->hasFile('avatar') || !$request->file('avatar')->isValid()) {
            return response()->json([
                'message' => 'Invalid file',
            ], 400);
        }
        $result = $this->userService->uploadAvatar($user->id, $request->file('avatar'));

        if (!$result || !$result['success']) {
            return response()->json([
                'message' => $result['message'] ?? 'Upload avatar thất bại'
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'avatar_url' => $result['avatar_url'],
            'user' => $user->fresh(['media']),
        ], 200);
    }
}
