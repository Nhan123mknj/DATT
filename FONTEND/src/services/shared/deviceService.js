import apiClient from '../api/apiClient'

export const deviceService = {
    getCategories() {
        return apiClient.get('/device-categories')
    },

    getDevicesByCategory(categoryId) {
        return apiClient.get(`/device-categories/${categoryId}/devices`)
    },

    getDeviceUnitsByDevice(deviceId) {
        return apiClient.get(`/devices/${deviceId}/device-units`)
    },
}
