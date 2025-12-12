<?php

namespace App\Http\Controllers\Api\Borrower;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\BorrowerDashboardService;

class DashboardController extends Controller
{
    protected BorrowerDashboardService $dashboardService;

    public function __construct(BorrowerDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get borrower dashboard statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $userId = auth()->id();
            $stats = $this->dashboardService->getStatistics($userId);

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
