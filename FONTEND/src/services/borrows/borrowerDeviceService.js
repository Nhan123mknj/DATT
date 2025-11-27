import apiClient from '../api/apiClient'

const BASE_URL = '/borrower'

export const borrowerDeviceService = {
  getCategories() {
    return apiClient.get(`${BASE_URL}/device-categories`)
  },

  getDevicesByCategory(categoryId) {
    return apiClient.get(`${BASE_URL}/device-categories/${categoryId}/devices`)
  },

  getDeviceUnitsByDevice(deviceId) {
    return apiClient.get(`${BASE_URL}/devices/${deviceId}/units`)
  },
}

