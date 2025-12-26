<?php

namespace App\Http\Controllers\Api\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Borrows;
use App\Services\Dashboard\BorrowerDashboardService;
use Illuminate\Support\Facades\Request;

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
    public function getDeviceBorrows(Request $request)
    {
        $userId = auth()->id();
        $result = Borrows::with('details.deviceUnit')
            ->whereNotNull('issued_at')
            ->whereDoesntHave('returnSlip')
            ->where('borrower_id', $userId)
            ->get();
        $totalDevices = $result->sum(function ($borrow) {
            return $borrow->details->count();
        });
        return response()->json([
            // 'count' => $result->count(),
            'total_devices' => $totalDevices,
            'data' => $result
        ], 200);
    }
}
