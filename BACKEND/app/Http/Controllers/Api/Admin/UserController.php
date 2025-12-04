<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    protected UserService $userService;


    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'name', 'email', 'role', 'is_active',
            'from_date', 'to_date',
            'order_by', 'direction',
            'search'
        ]);

        $perPage = $request->get('per_page', 10);

        $users = $this->userService->getAllUser($filters, $perPage);

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found'], 404);
        }

        return response()->json($users, 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->name . "123"),
            'role' => $request->role,
        ]);
        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|required|in:admin,borrower,staff',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

        $user = $this->userService->updateUser($id, $data);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = $this->userService->deleteUser($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function resetPassword($id)
    {
        $result = $this->userService->resetPassword($id);

        if (!$result) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'message' => 'Password has been reset',
            'note' => 'Check your email for the new password',
        ], 200);
    }

    public function toggleStatus($id)
    {
        $result = $this->userService->toggleActiveStatus($id);
        if (!$result) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json([
            'message' => $result['message'],
            'user'    => $result['user']
        ], 200);
    }

    /**
     * Upload avatar for user
     */
    public function uploadAvatar(UploadAvatarRequest $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $result = $this->userService->uploadAvatar($id, $request->file('avatar'));

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
