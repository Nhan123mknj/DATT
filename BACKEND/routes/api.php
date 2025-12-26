<?php

use App\Http\Controllers\Api\Admin\DeviceCategoriesController;
use App\Http\Controllers\Api\Admin\DeviceController;
use App\Http\Controllers\Api\Admin\DeviceUnitsController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Borrower\BorrowsController;
use App\Http\Controllers\Api\Borrower\DashboardController as BorrowerDashboardController;
use App\Http\Controllers\Api\Staff\BorrowsController as StaffBorrowsController;
use App\Http\Controllers\Api\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Api\Staff\ReservationController as StaffReservationController;
use App\Http\Controllers\Api\Shared\DeviceController as SharedDeviceController;
use App\Http\Controllers\Api\Shared\MediaController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\MenuController;

use App\Http\Controllers\Api\Admin\ReservationsController;
use App\Http\Controllers\Api\Borrower\ReservationController;
use App\Http\Controllers\Api\Borrower\ReturnSlipController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DeviceMaintenanceController;
use App\Http\Controllers\Api\BorrowReturnController;


Route::middleware(['auth:api'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});


Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('dashboard/statistics', [AdminDashboardController::class, 'statistics']);
    Route::get('reservations', [ReservationsController::class, 'index']);
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
    Route::get('device/export/excel', [DeviceController::class, 'export']);
    Route::post('device/import/excel', [DeviceController::class, 'import']);
    // Route::get('device/{id}/preview-delete', [DeviceController::class, 'previewDelete']);
    // Route::get('device/{id}/edit', [DeviceController::class, 'edit']);
    Route::get('device-units/export-borrowers', [DeviceUnitsController::class, 'exportBorrowers']);
    Route::apiResource('device-units', DeviceUnitsController::class)->except(['destroy']);
    Route::post('device-units/{id}/retire', [DeviceUnitsController::class, 'retire']);
    Route::post('device-units/bulk-retire', [DeviceUnitsController::class, 'bulkRetire']);
    Route::get('device-units/{id}/activity-log', [DeviceUnitsController::class, 'activityLog']);
    Route::get('device-units/{id}/damage-history', [DeviceUnitsController::class, 'damageHistory']);
    Route::get('device-units/export/excel', [DeviceUnitsController::class, 'export']);
    Route::post('device-units/import/excel', [DeviceUnitsController::class, 'import']);


    // Reports
    Route::get('reports/dashboard-stats', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getDashboardStats']);
    Route::get('reports/device-damage', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getDeviceDamageReports']);
    Route::get('reports/device-damage/device/{deviceUnitId}', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getDeviceDamageHistory']);
    Route::get('reports/device-damage/{id}', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getDeviceDamageDetail']);
    Route::get('reports/user-activity/{userId}', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getUserActivityReport']);
    Route::get('reports/borrow-statistics', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getBorrowStatistics']);
    Route::get('reports/activity-logs', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getActivityLogs']);
    Route::get('reports/stock', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getStockReport']);
    Route::get('reports/borrows', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getDetailBorrowed']);
    Route::get('reports/returns', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getDetailReturns']);
    Route::get('reports/reservations', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getReserveList']);

    Route::get('menus', [MenuController::class, 'index']);
    Route::post('menus', [MenuController::class, 'store']);
    Route::get('menus/{id}', [MenuController::class, 'show']);
    Route::put('menus/{id}', [MenuController::class, 'update']);
    Route::delete('menus/{id}', [MenuController::class, 'destroy']);


    Route::post('menu-items', [MenuController::class, 'storeItem']);
    Route::put('menu-items/{id}', [MenuController::class, 'updateItem']);
    Route::delete('menu-items/{id}', [MenuController::class, 'destroyItem']);
    Route::post('menu-items/reorder', [MenuController::class, 'reorder']);

    Route::get('maintenances', [DeviceMaintenanceController::class, 'index']);
    Route::post('maintenances', [DeviceMaintenanceController::class, 'store']);
    Route::get('maintenances/{id}', [DeviceMaintenanceController::class, 'show']);
    Route::put('maintenances/{id}', [DeviceMaintenanceController::class, 'update']);
    Route::delete('maintenances/{id}', [DeviceMaintenanceController::class, 'destroy']);
    Route::post('maintenances/{id}/complete', [DeviceMaintenanceController::class, 'complete']);
});


Route::middleware(['auth:api'])->group(function () {
    Route::get('menus/{slug}', [MenuController::class, 'getBySlug']);
});

Route::prefix('borrower')->middleware(['auth:api', 'role:student,teacher,admin'])->group(function () {

    Route::get('dashboard/statistics', [BorrowerDashboardController::class, 'statistics']);
    Route::get('dashboard/device-borrows', [BorrowerDashboardController::class, 'getDeviceBorrows']);
    Route::apiResource('borrows', BorrowsController::class);
    Route::get('reservations', [ReservationController::class, 'index']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/{id}', [ReservationController::class, 'show']);
    Route::put('reservations/{id}', [ReservationController::class, 'update']);
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::get('reports/borrows/me', [App\Http\Controllers\Api\Admin\ReportsController::class, 'getBorrowedByUser']);
    Route::get('device-categories', [SharedDeviceController::class, 'categories']);
    Route::get('device-categories/{id}/devices', [SharedDeviceController::class, 'devicesByCategory']);
    Route::get('devices/{id}/device-units', [SharedDeviceController::class, 'deviceUnitsByDevice']);
    Route::get('return-slips', [ReturnSlipController::class, 'index']);
    Route::get('return-slips/{id}', [ReturnSlipController::class, 'show']);
});

Route::prefix('staff')->middleware(['auth:api', 'role:staff,admin'])->group(function () {

    Route::get('dashboard/statistics', [StaffDashboardController::class, 'statistics']);

    Route::get('reservations', [StaffReservationController::class, 'index']);
    Route::get('reservations/statistics', [StaffReservationController::class, 'statistics']);
    Route::get('reservations/export', [StaffReservationController::class, 'export']);
    Route::get('reservations/{id}', [StaffReservationController::class, 'show']);
    Route::post('reservations/{id}/approve', [StaffReservationController::class, 'approve']);
    Route::post('reservations/{id}/reject', [StaffReservationController::class, 'reject']);
    Route::post('reservations/{id}/create-borrow', [StaffReservationController::class, 'createBorrowManually']);


    Route::get('borrows/export', [StaffBorrowsController::class, 'export']);
    Route::apiResource('borrows', StaffBorrowsController::class);
    Route::post('borrows/{id}/approve', [StaffBorrowsController::class, 'approve']);
    Route::post('borrows/{id}/reject', [StaffBorrowsController::class, 'reject']);
    Route::post('borrows/{id}/cancel', [StaffBorrowsController::class, 'cancel']);
    Route::post('borrows/{id}/issue', [StaffBorrowsController::class, 'issue']);
    Route::post('borrows/{id}/send-otp', [StaffBorrowsController::class, 'sendOtp']);
    Route::post('borrows/{id}/send-return-otp', [StaffBorrowsController::class, 'sendReturnOtp']);
    Route::post('borrows/{id}/return', [StaffBorrowsController::class, 'processReturn']);

    // Return Slips
    Route::get('return-slips', [\App\Http\Controllers\Api\Staff\ReturnSlipController::class, 'index']);
    Route::post('return-slips', [\App\Http\Controllers\Api\Staff\ReturnSlipController::class, 'store']);
    Route::get('return-slips/{id}', [\App\Http\Controllers\Api\Staff\ReturnSlipController::class, 'show']);

    Route::get('users', [App\Http\Controllers\Api\Staff\UserController::class, 'index']);
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
Route::middleware(['auth:api'])->group(function () {
    Route::get('device-categories', [SharedDeviceController::class, 'categories']);
    Route::get('device-categories/{id}/devices', [SharedDeviceController::class, 'devicesByCategory']);
    Route::get('devices/{id}/device-units', [SharedDeviceController::class, 'deviceUnitsByDevice']);
    Route::apiResource('maintenances', DeviceMaintenanceController::class);
});
