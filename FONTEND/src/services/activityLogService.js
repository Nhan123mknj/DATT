import apiClient from '../services/api/apiClient';

export const activityLogService = {

  getDeviceUnitActivityLog(deviceUnitId) {
    return apiClient.get(`/admin/device-units/${deviceUnitId}/activity-log`);
  },


  getDeviceUnitDamageHistory(deviceUnitId) {
    return apiClient.get(`/admin/device-units/${deviceUnitId}/damage-history`);
  },
};
