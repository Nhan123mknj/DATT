<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getAllUser()
    {
        return User::all();
    }
    public function getUserById($id)
    {
        return User::find($id);
    }
    public function createUser(array $data)
    {
        return User::create($data);
    }
    public function updateUser($id, array $data)
    {
        $user = User::find($id);

        if (!$user) {
            return null;
        }

        if (isset($data['reset_password']) && $data['reset_password']) {
            $data['password'] = bcrypt($user->name . "@123");
            unset($data['reset_password']);
        }

        $user->update($data);
        return $user->fresh();
    }
    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return true;
        }
        return false;
    }
    public function resetPassword($id, $resetPassword = false)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        $newpassword = $user->name . '@123';

        $user->password = bcrypt($newpassword);
        $user->save();
        return [
            'message' => 'Password reset successfully',
            'user' => $user,
            'new_password' => $newpassword,
        ];
    }

    public function toggleActiveStatus($id)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        $user->is_active = !$user->is_active;
        $user->save();
        return [
            'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully',
            'user'    => $user
        ];
    }
}
