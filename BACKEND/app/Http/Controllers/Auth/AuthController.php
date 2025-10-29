<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected AuthService $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $result = $this->authService->login($request->all());

        return response()->json(
            $result,
            $result['status'] ?? 200
        );
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    public function register(Request $request)
    {
        $result = $this->authService->register($request->all());
        return response()->json($result, $result['status']);
    }

    public function refresh()
    {
        return response()->json($this->authService->refresh());
    }

    public function userProfile()
    {
        return response()->json(auth('api')->user());
    }

    public function changePassword(Request $request)
    {
        $result = $this->authService->changePassword($request->all());
        return response()->json($result, $result['status'] ?? 200);
    }
}
