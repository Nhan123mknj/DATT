import apiClient from '../apiClient';

/**
 * Activity Log Service
 * Handles API calls for activity logs
 */
export const activityLogService = {
  /**
   * Get activity logs with filters
   * @param {Object} filters - Filter parameters
   * @returns {Promise}
   */
  async getActivityLogs(filters = {}) {
    const params = new URLSearchParams();
    
    if (filters.period) params.append('period', filters.period);
    if (filters.from_date) params.append('from_date', filters.from_date);
    if (filters.to_date) params.append('to_date', filters.to_date);
    if (filters.log_name) params.append('log_name', filters.log_name);
    if (filters.event) params.append('event', filters.event);
    if (filters.causer_id) params.append('causer_id', filters.causer_id);
    if (filters.search) params.append('search', filters.search);
    if (filters.page) params.append('page', filters.page);
    
    const response = await apiClient.get(`/admin/reports/activity-logs?${params.toString()}`);
    return response.data;
  },
};

export default activityLogService;
