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

  remove(id) {
    return apiClient.delete(`${BASE_URL}/${id}`)
  },
}
