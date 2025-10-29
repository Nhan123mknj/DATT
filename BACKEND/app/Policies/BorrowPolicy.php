<?php

namespace App\Policies;

use App\Models\Borrows;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class BorrowPolicy
{
    private function isActive(User $user): bool
    {
        return $user->is_active == 1;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if (!$this->isActive($user)) {
            return false;
        }

        if ($user->role === 'borrower') {
            return true;
        }
        return in_array($user->role, ['admin', 'staff']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Borrows $borrows): bool
    {
        if (!$this->isActive($user)) {
            return false;
        }

        if ($user->role === 'borrower') {
            return $borrows->borrower_id === $user->id;
        }
        return in_array($user->role, ['admin', 'staff']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (!$this->isActive($user)) {
            return false;
        }

        $cancelCount = Borrows::where('borrower_id', $user->id)
            ->where('status', 'canceled')
            ->whereDate('updated_at', today())
            ->count();

        if ($cancelCount >= 3) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Borrows $borrows): bool
    {
        if ($user->role === 'borrower') {
            return $borrows->borrower_id === $user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Borrows $borrows): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Borrows $borrows): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Borrows $borrows): bool
    {
        return false;
    }
}
