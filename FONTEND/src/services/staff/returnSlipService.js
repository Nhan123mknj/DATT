import apiClient from '../api/apiClient'

export const returnSlipService = {
  getAll(params = {}) {
    return apiClient.get('/staff/return-slips', { params })
  },

  getById(id) {
    return apiClient.get(`/staff/return-slips/${id}`)
  },
  create(data) {
    return apiClient.post('/staff/return-slips', data)
  },
}
