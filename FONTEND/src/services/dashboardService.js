import apiClient from '../services/api/apiClient';

export const dashboardService = {

  getAdminStatistics() {
    return apiClient.get('/admin/dashboard/statistics');
  },

  getStaffStatistics() {
    return apiClient.get('/staff/dashboard/statistics');
  },

  getBorrowerStatistics() {
    return apiClient.get('/borrower/dashboard/statistics');
  },
};
