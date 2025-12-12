<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

class BorrowerDashboardService
{
    /**
     * Get comprehensive statistics for borrower dashboard
     * 
     * @param int $userId
     * @return array
     */
    public function getStatistics(int $userId): array
    {
        $reservationStats = $this->getReservationStatistics($userId);
        $borrowStats = $this->getBorrowStatistics($userId);
        $recentActivity = $this->getRecentActivity($userId);
        $deviceUsage = $this->getDeviceUsageByCategory($userId);

        return [
            'reservations' => $reservationStats,
            'borrows' => $borrowStats,
            'recent_activity' => $recentActivity,
            'device_usage' => $deviceUsage,
        ];
    }

    /**
     * Get reservation statistics using raw SQL
     * 
     * @param int $userId
     * @return array
     */
    private function getReservationStatistics(int $userId): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(*) as total_reservations,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reservations,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_reservations,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_reservations,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_reservations,
                COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_reservations
            FROM device_reservations
            WHERE user_id = ?
        ", [$userId]);

        return [
            'total' => $result->total_reservations ?? 0,
            'pending' => $result->pending_reservations ?? 0,
            'approved' => $result->approved_reservations ?? 0,
            'rejected' => $result->rejected_reservations ?? 0,
            'cancelled' => $result->cancelled_reservations ?? 0,
            'expired' => $result->expired_reservations ?? 0,
        ];
    }

    /**
     * Get borrow statistics using raw SQL
     * 
     * @param int $userId
     * @return array
     */
    private function getBorrowStatistics(int $userId): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(*) as total_borrows,
                COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as active_borrows,
                COUNT(CASE WHEN status = 'returned' THEN 1 END) as returned_borrows,
                COUNT(CASE WHEN status = 'borrowed' AND expected_return_date < NOW() THEN 1 END) as overdue_borrows,
                COUNT(CASE WHEN status = 'borrowed' AND expected_return_date >= NOW() THEN 1 END) as on_time_borrows
            FROM borrows
            WHERE borrower_id = ?
        ", [$userId]);

        return [
            'total' => $result->total_borrows ?? 0,
            'active' => $result->active_borrows ?? 0,
            'returned' => $result->returned_borrows ?? 0,
            'overdue' => $result->overdue_borrows ?? 0,
            'on_time' => $result->on_time_borrows ?? 0,
        ];
    }

    /**
     * Get recent activity timeline (last 7 days)
     * 
     * @param int $userId
     * @return array
     */
    private function getRecentActivity(int $userId): array
    {
        $activities = DB::select("
            SELECT 
                DATE(created_at) as activity_date,
                COUNT(*) as count
            FROM (
                SELECT created_at, 'reservation' as type
                FROM device_reservations
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                
                UNION ALL
                
                SELECT created_at, 'borrow' as type
                FROM borrows
                WHERE borrower_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ) as combined_activity
            GROUP BY DATE(created_at)
            ORDER BY activity_date DESC
            LIMIT 7
        ", [$userId, $userId]);

        return array_map(function ($item) {
            return [
                'date' => $item->activity_date,
                'count' => $item->count,
            ];
        }, $activities);
    }

    /**
     * Get device usage summary by category
     * 
     * @param int $userId
     * @return array
     */
    private function getDeviceUsageByCategory(int $userId): array
    {
        $usage = DB::select("
            SELECT 
                dc.name as category_name,
                COUNT(DISTINCT bd.device_unit_id) as devices_borrowed,
                COUNT(bd.id) as total_borrows
            FROM borrow_details bd
            INNER JOIN borrows b ON bd.borrow_id = b.id
            INNER JOIN device_units du ON bd.device_unit_id = du.id
            INNER JOIN devices d ON du.device_id = d.id
            INNER JOIN device_categories dc ON d.category_id = dc.id
            WHERE b.borrower_id = ?
            GROUP BY dc.id, dc.name
            ORDER BY total_borrows DESC
            LIMIT 5
        ", [$userId]);

        return array_map(function ($item) {
            return [
                'category' => $item->category_name,
                'devices_borrowed' => $item->devices_borrowed,
                'total_borrows' => $item->total_borrows,
            ];
        }, $usage);
    }
}
