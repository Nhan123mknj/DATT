<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

class StaffDashboardService
{
    /**
     * Get comprehensive statistics for staff dashboard
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $reservationStats = $this->getReservationStatistics();
        $borrowStats = $this->getBorrowStatistics();
        $todayActivity = $this->getTodayActivity();
        $weeklyTrends = $this->getWeeklyTrends();
        $topBorrowedDevices = $this->getTopBorrowedDevices();

        return [
            'reservations' => $reservationStats,
            'borrows' => $borrowStats,
            'today' => $todayActivity,
            'weekly_trends' => $weeklyTrends,
            'top_devices' => $topBorrowedDevices,
        ];
    }

    /**
     * Get reservation statistics
     * 
     * @return array
     */
    private function getReservationStatistics(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(*) as total_reservations,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reservations,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_reservations,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_reservations,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_reservations
            FROM device_reservations
        ");

        return [
            'total' => $result->total_reservations ?? 0,
            'pending' => $result->pending_reservations ?? 0,
            'approved' => $result->approved_reservations ?? 0,
            'rejected' => $result->rejected_reservations ?? 0,
            'cancelled' => $result->cancelled_reservations ?? 0,
        ];
    }

    /**
     * Get borrow statistics
     * 
     * @return array
     */
    private function getBorrowStatistics(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(*) as total_borrows,
                COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as active_borrows,
                COUNT(CASE WHEN status = 'returned' THEN 1 END) as returned_borrows,
                COUNT(CASE WHEN status = 'borrowed' AND expected_return_date < NOW() THEN 1 END) as overdue_borrows
            FROM borrows
        ");

        return [
            'total' => $result->total_borrows ?? 0,
            'active' => $result->active_borrows ?? 0,
            'returned' => $result->returned_borrows ?? 0,
            'overdue' => $result->overdue_borrows ?? 0,
        ];
    }

    /**
     * Get today's activity statistics
     * 
     * @return array
     */
    private function getTodayActivity(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as borrows_created_today,
                COUNT(CASE WHEN DATE(actual_return_date) = CURDATE() THEN 1 END) as returns_processed_today
            FROM borrows
        ");

        $reservations = DB::selectOne("
            SELECT COUNT(*) as reservations_created_today
            FROM device_reservations
            WHERE DATE(created_at) = CURDATE()
        ");

        return [
            'borrows_issued' => $result->borrows_created_today ?? 0,
            'returns_processed' => $result->returns_processed_today ?? 0,
            'reservations_created' => $reservations->reservations_created_today ?? 0,
        ];
    }

    /**
     * Get weekly trends (last 7 days)
     * 
     * @return array
     */
    private function getWeeklyTrends(): array
    {
        $trends = DB::select("
            SELECT 
                DATE(created_at) as date,
                COUNT(CASE WHEN status IN ('borrowed', 'returned') THEN 1 END) as borrows,
                COUNT(CASE WHEN status = 'returned' AND DATE(actual_return_date) = DATE(created_at) THEN 1 END) as returns
            FROM borrows
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");

        return array_map(function ($item) {
            return [
                'date' => $item->date,
                'borrows' => $item->borrows ?? 0,
                'returns' => $item->returns ?? 0,
            ];
        }, $trends);
    }

    /**
     * Get top borrowed devices
     * 
     * @return array
     */
    private function getTopBorrowedDevices(): array
    {
        $devices = DB::select("
            SELECT 
                d.name as device_name,
                dc.name as category_name,
                COUNT(bd.id) as borrow_count,
                COUNT(DISTINCT b.borrower_id) as unique_borrowers
            FROM borrow_details bd
            INNER JOIN borrows b ON bd.borrow_id = b.id
            INNER JOIN device_units du ON bd.device_unit_id = du.id
            INNER JOIN devices d ON du.device_id = d.id
            INNER JOIN device_categories dc ON d.category_id = dc.id
            WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY d.id, d.name, dc.name
            ORDER BY borrow_count DESC
            LIMIT 10
        ");

        return array_map(function ($item) {
            return [
                'device_name' => $item->device_name,
                'category' => $item->category_name,
                'borrow_count' => $item->borrow_count,
                'unique_borrowers' => $item->unique_borrowers,
            ];
        }, $devices);
    }
}
