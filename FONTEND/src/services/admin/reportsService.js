import apiClient from '../api/apiClient';

export const reportsService = {
  /**
   * Get dashboard statistics
   */
  getDashboardStats() {
    return apiClient.get('/admin/reports/dashboard-stats');
  },

  /**
   * Get device damage reports
   */
  getDeviceDamageReports(filters = {}) {
    return apiClient.get('/admin/reports/device-damage', { params: filters });
  },

  /**
   * Get borrow statistics
   */
  getBorrowStatistics(filters = {}) {
    return apiClient.get('/admin/reports/borrow-statistics', { params: filters });
  },

  /**
   * Get activity logs
   */
  getActivityLogs(filters = {}) {
    return apiClient.get('/admin/reports/activity-logs', { params: filters });
  },

  /**
   * Get stock report
   */
  getStockReport(startDate, endDate) {
    return apiClient.get('/admin/reports/stock', { 
      params: { start_date: startDate, end_date: endDate } 
    });
  },
  getDetailBorrows() {
    return apiClient.get('/admin/reports/borrows');
  }
};
