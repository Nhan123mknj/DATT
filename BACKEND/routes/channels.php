<?php

use App\Models\Borrows;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('borrow.{borrowId}', function ($user, $borrowId) {
    return $user->id === Borrows::findOrFail($borrowId)->user_id;
});
