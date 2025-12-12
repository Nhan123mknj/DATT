<?php

use App\Models\Borrows;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;


Broadcast::channel('user.{userId}', function ($user, $userId) {
    Log::info('[Broadcast] User channel auth attempt', [
        'authenticated_user_id' => $user?->id,
        'requested_user_id' => $userId,
        'user_role' => $user?->role,
        'match' => (int) $user?->id === (int) $userId
    ]);
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('staff.notifications', function ($user) {
    Log::info('[Broadcast] Staff channel auth attempt', [
        'user_id' => $user?->id,
        'user_role' => $user?->role,
        'authorized' => $user?->role === 'staff' || $user?->role === 'admin'
    ]);
    return $user->role === 'staff' || $user->role === 'admin';
});
