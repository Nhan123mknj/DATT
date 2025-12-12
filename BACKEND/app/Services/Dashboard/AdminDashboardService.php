<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Get comprehensive statistics for admin dashboard
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $userStats = $this->getUserStatistics();
        $deviceStats = $this->getDeviceStatistics();
        $borrowStats = $this->getBorrowStatistics();
        $reservationStats = $this->getReservationStatistics();
        $monthlyTrends = $this->getMonthlyTrends();
        $topBorrowers = $this->getTopBorrowers();
        $deviceUtilization = $this->getDeviceUtilization();

        return [
            'users' => $userStats,
            'devices' => $deviceStats,
            'borrows' => $borrowStats,
            'reservations' => $reservationStats,
            'monthly_trends' => $monthlyTrends,
            'top_borrowers' => $topBorrowers,
            'device_utilization' => $deviceUtilization,
        ];
    }

    /**
     * Get user statistics
     * 
     * @return array
     */
    private function getUserStatistics(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'student' THEN 1 END) as total_students,
                COUNT(CASE WHEN role = 'teacher' THEN 1 END) as total_teachers,
                COUNT(CASE WHEN role = 'staff' THEN 1 END) as total_staff,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins,
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users,
                COUNT(CASE WHEN is_active = 0 THEN 1 END) as inactive_users
            FROM users
        ");

        return [
            'total' => $result->total_users ?? 0,
            'students' => $result->total_students ?? 0,
            'teachers' => $result->total_teachers ?? 0,
            'staff' => $result->total_staff ?? 0,
            'admins' => $result->total_admins ?? 0,
            'active' => $result->active_users ?? 0,
            'inactive' => $result->inactive_users ?? 0,
        ];
    }

    /**
     * Get device statistics
     * 
     * @return array
     */
    private function getDeviceStatistics(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(DISTINCT d.id) as total_devices,
                COUNT(DISTINCT du.id) as total_device_units,
                COUNT(DISTINCT dc.id) as total_categories,
                COUNT(CASE WHEN du.id IN (
                    SELECT device_unit_id FROM borrow_details bd
                    INNER JOIN borrows b ON bd.borrow_id = b.id
                    WHERE b.status = 'borrowed'
                ) THEN 1 END) as units_borrowed,
                COUNT(CASE WHEN du.id NOT IN (
                    SELECT device_unit_id FROM borrow_details bd
                    INNER JOIN borrows b ON bd.borrow_id = b.id
                    WHERE b.status = 'borrowed'
                ) THEN 1 END) as units_available
            FROM devices d
            LEFT JOIN device_units du ON d.id = du.device_id
            LEFT JOIN device_categories dc ON d.category_id = dc.id
        ");

        return [
            'total_devices' => $result->total_devices ?? 0,
            'total_units' => $result->total_device_units ?? 0,
            'categories' => $result->total_categories ?? 0,
            'units_borrowed' => $result->units_borrowed ?? 0,
            'units_available' => $result->units_available ?? 0,
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
                COUNT(CASE WHEN status = 'returned' THEN 1 END) as completed_borrows,
                COUNT(CASE WHEN status = 'borrowed' AND expected_return_date < NOW() THEN 1 END) as overdue_borrows
            FROM borrows
        ");

        return [
            'total' => $result->total_borrows ?? 0,
            'active' => $result->active_borrows ?? 0,
            'completed' => $result->completed_borrows ?? 0,
            'overdue' => $result->overdue_borrows ?? 0,
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
     * Get monthly trends (last 6 months)
     * 
     * @return array
     */
    private function getMonthlyTrends(): array
    {
        $trends = DB::select("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as total_borrows,
                COUNT(CASE WHEN status = 'returned' THEN 1 END) as completed,
                COUNT(DISTINCT borrower_id) as unique_borrowers
            FROM borrows
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ");

        return array_map(function ($item) {
            return [
                'month' => $item->month,
                'total_borrows' => $item->total_borrows,
                'completed' => $item->completed,
                'unique_borrowers' => $item->unique_borrowers,
            ];
        }, $trends);
    }

    /**
     * Get top borrowers
     * 
     * @return array
     */
    private function getTopBorrowers(): array
    {
        $borrowers = DB::select("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.role,
                COALESCE(s.student_code, t.teacher_code) as user_code,
                COUNT(b.id) as total_borrows,
                COUNT(CASE WHEN b.status = 'borrowed' THEN 1 END) as active_borrows
            FROM users u
            LEFT JOIN students s ON u.id = s.user_id
            LEFT JOIN teachers t ON u.id = t.user_id
            INNER JOIN borrows b ON u.id = b.borrower_id
            WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.id, u.name, u.email, u.role, user_code
            ORDER BY total_borrows DESC
            LIMIT 10
        ");

        return array_map(function ($item) {
            return [
                'user_id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'role' => $item->role,
                'code' => $item->user_code,
                'total_borrows' => $item->total_borrows,
                'active_borrows' => $item->active_borrows,
            ];
        }, $borrowers);
    }

    /**
     * Get device utilization rate
     * 
     * @return array
     */
    private function getDeviceUtilization(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(DISTINCT du.id) as total_units,
                COUNT(DISTINCT CASE WHEN b.status = 'borrowed' THEN bd.device_unit_id END) as borrowed_units,
                ROUND(
                    (COUNT(DISTINCT CASE WHEN b.status = 'borrowed' THEN bd.device_unit_id END) / COUNT(DISTINCT du.id)) * 100,
                    2
                ) as utilization_rate
            FROM device_units du
            LEFT JOIN borrow_details bd ON du.id = bd.device_unit_id
            LEFT JOIN borrows b ON bd.borrow_id = b.id AND b.status = 'borrowed'
        ");

        return [
            'total_units' => $result->total_units ?? 0,
            'borrowed_units' => $result->borrowed_units ?? 0,
            'utilization_rate' => $result->utilization_rate ?? 0,
        ];
    }
}
