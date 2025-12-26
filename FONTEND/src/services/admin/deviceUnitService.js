import apiClient from '../api/apiClient'

const BASE_URL = '/admin/device-units'

export const deviceUnitService = {
  list(params = {}) {
    return apiClient.get(BASE_URL, { params })
  },

  show(id) {
    return apiClient.get(`${BASE_URL}/${id}`)
  },

  create(payload) {
    return apiClient.post(BASE_URL, payload)
  },

  update(id, payload) {
    return apiClient.put(`${BASE_URL}/${id}`, payload)
  },

  retire(id, data = {}) {
    return apiClient.post(`${BASE_URL}/${id}/retire`, data)
  },

  // bulkRetire(deviceUnitIds, params={}) {
  //   return apiClient.post(`${BASE_URL}/bulk-retire`, {
  //     device_unit_ids: deviceUnitIds,
  //     retire_reason: retireReason
  //   })
  // },

  exportExcel() {
    return apiClient.get(`${BASE_URL}/export/excel`, {
      responseType: 'blob'
    })
  },

  importExcel(formData) {
    return apiClient.post(`${BASE_URL}/import/excel`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
}
