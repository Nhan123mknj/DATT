<?php

use App\Http\Controllers\Api\Admin\DeviceCategoriesController;
use App\Http\Controllers\Api\Admin\DeviceController;
use App\Http\Controllers\Api\Admin\DeviceUnitsController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {

    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::patch('users/{id}/toogle-status', [UserController::class, 'toggleStatus']);


    Route::get('device-categories', [DeviceCategoriesController::class, 'index']);
    Route::post('device-categories', [DeviceCategoriesController::class, 'store']);
    Route::get('device-categories/{id}', [DeviceCategoriesController::class, 'show']);
    Route::put('device-categories/{id}', [DeviceCategoriesController::class, 'update']);
    Route::delete('device-categories/{id}', [DeviceCategoriesController::class, 'destroy']);
    Route::get('device-categories/{id}/preview-delete', [DeviceCategoriesController::class, 'previewDelete']);
    Route::get('device-categories/{id}/edit', [DeviceCategoriesController::class, 'edit']);

    // Similarly, you can add routes for devices and device units here
    Route::get('device', [DeviceController::class, 'index']);
    Route::post('device', [DeviceController::class, 'store']);
    Route::get('device/{id}', [DeviceController::class, 'show']);
    Route::put('device/{id}', [DeviceController::class, 'update']);
    Route::delete('device/{id}', [DeviceController::class, 'destroy']);
    // Route::get('device/{id}/preview-delete', [DeviceController::class, 'previewDelete']);
    // Route::get('device/{id}/edit', [DeviceController::class, 'edit']);

    Route::apiResource('device-units', DeviceUnitsController::class);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware(['auth:api', 'check_active'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
        Route::post('/change-pass', [AuthController::class, 'changePassWord']);
    });
});
