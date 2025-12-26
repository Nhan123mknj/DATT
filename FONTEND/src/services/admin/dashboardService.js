import  apiClient  from '../api/apiClient';

/**
 * Admin Dashboard Service
 * Fetch comprehensive dashboard statistics from backend
 */
const dashboardService = {
  /**
   * Get all dashboard statistics
   * @returns {Promise<Object>} Dashboard statistics
   */
  getStatistics() {
    return apiClient.get('/admin/dashboard/statistics');
  },
};

export { dashboardService };
