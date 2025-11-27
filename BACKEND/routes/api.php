<?php

use App\Http\Controllers\Api\Admin\DeviceCategoriesController;
use App\Http\Controllers\Api\Admin\DeviceController;
use App\Http\Controllers\Api\Admin\DeviceUnitsController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Borrower\BorrowController;
use App\Http\Controllers\Api\Staff\BorrowsController as StaffBorrowsController;
use App\Http\Controllers\Api\Staff\ReservationController as StaffReservationController;
use App\Http\Controllers\Api\Borrower\DeviceController as BorrowerDeviceController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\MenuController;
use App\Http\Controllers\Api\Borrower\ReservationController;

Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {

    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::patch('users/{id}/toogle-status', [UserController::class, 'toggleStatus']);
    Route::post('users/{id}/upload-avatar', [UserController::class, 'uploadAvatar']);


    Route::get('device-categories', [DeviceCategoriesController::class, 'index']);
    Route::post('device-categories', [DeviceCategoriesController::class, 'store']);
    Route::get('device-categories/{id}', [DeviceCategoriesController::class, 'show']);
    Route::put('device-categories/{id}', [DeviceCategoriesController::class, 'update']);
    Route::delete('device-categories/{id}', [DeviceCategoriesController::class, 'destroy']);
    Route::get('device-categories/{id}/preview-delete', [DeviceCategoriesController::class, 'previewDelete']);
    Route::get('device-categories/{id}/edit', [DeviceCategoriesController::class, 'edit']);


    Route::get('device', [DeviceController::class, 'index']);
    Route::post('device', [DeviceController::class, 'store']);
    Route::get('device/{id}', [DeviceController::class, 'show']);
    Route::put('device/{id}', [DeviceController::class, 'update']);
    Route::delete('device/{id}', [DeviceController::class, 'destroy']);
    // Route::get('device/{id}/preview-delete', [DeviceController::class, 'previewDelete']);
    // Route::get('device/{id}/edit', [DeviceController::class, 'edit']);

    Route::apiResource('device-units', DeviceUnitsController::class);


    Route::get('menus', [MenuController::class, 'index']);
    Route::post('menus', [MenuController::class, 'store']);
    Route::get('menus/{id}', [MenuController::class, 'show']);
    Route::put('menus/{id}', [MenuController::class, 'update']);
    Route::delete('menus/{id}', [MenuController::class, 'destroy']);

    // Menu Items
    Route::post('menu-items', [MenuController::class, 'storeItem']);
    Route::put('menu-items/{id}', [MenuController::class, 'updateItem']);
    Route::delete('menu-items/{id}', [MenuController::class, 'destroyItem']);
    Route::post('menu-items/reorder', [MenuController::class, 'reorder']);
});


Route::middleware(['auth:api'])->group(function () {
    Route::get('menus/{slug}', [MenuController::class, 'getBySlug']);
});

Route::prefix('borrower')->middleware(['auth:api', 'role:borrower,admin'])->group(function () {

    // Route::post('borrows', [BorrowController::class, 'store']);

    Route::get('reservations', [ReservationController::class, 'index']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/{id}', [ReservationController::class, 'show']);
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel']);

    // Device selection endpoints
    Route::get('device-categories', [BorrowerDeviceController::class, 'categories']);
    Route::get('device-categories/{categoryId}/devices', [BorrowerDeviceController::class, 'devicesByCategory']);
    Route::get('devices/{deviceId}/units', [BorrowerDeviceController::class, 'deviceUnitsByDevice']);
});

Route::prefix('staff')->middleware(['auth:api', 'role:staff,admin'])->group(function () {

    Route::get('reservations', [StaffReservationController::class, 'index']);
    Route::get('reservations/statistics', [StaffReservationController::class, 'statistics']);
    Route::get('reservations/{id}', [StaffReservationController::class, 'show']);
    Route::post('reservations/{id}/approve', [StaffReservationController::class, 'approve']);
    Route::post('reservations/{id}/reject', [StaffReservationController::class, 'reject']);
    Route::post('reservations/{id}/create-borrow', [StaffReservationController::class, 'createBorrowManually']);
    Route::apiResource('borrows', StaffBorrowsController::class);
    Route::post('borrows/{id}/approve', [StaffBorrowsController::class, 'approve']);
    Route::post('borrows/{id}/reject', [StaffBorrowsController::class, 'reject']);
    Route::post('borrows/{id}/return', [StaffBorrowsController::class, 'processReturn']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware(['auth:api', 'check_active'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
        Route::post('/user-profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/change-pass', [AuthController::class, 'changePassWord']);
        Route::get('/verify-access', [AuthController::class, 'verifyAccess']);
        Route::post('/upload-avatar', [AuthController::class, 'uploadAvatar']);
    });
});

// Media routes
Route::prefix('media')->middleware(['auth:api'])->group(function () {
    Route::post('/upload', [MediaController::class, 'upload']);
    Route::get('/', [MediaController::class, 'index']);
    Route::delete('/{id}', [MediaController::class, 'destroy']);
});
