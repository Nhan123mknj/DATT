<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\StaffDashboardService;

class DashboardController extends Controller
{
    protected StaffDashboardService $dashboardService;

    public function __construct(StaffDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get staff dashboard statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = $this->dashboardService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải thống kê',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
