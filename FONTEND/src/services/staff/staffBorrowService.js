import apiClient from '../api/apiClient'

export const staffBorrowService = {
  list(params = {}) {
    return apiClient.get('/staff/borrows', { params })
  },

  show(id) {
    return apiClient.get(`/staff/borrows/${id}`)
  },

  create(data) {
    return apiClient.post('/staff/borrows', data)
  },

  approve(id) {
    return apiClient.put(`/staff/borrows/${id}/approve`)
  },

  reject(id, data) {
    return apiClient.post(`/staff/borrows/${id}/reject`, data)
  },

  issue(id, otp) {
    return apiClient.post(`/staff/borrows/${id}/issue`, { otp })
  },

  cancel(id) {
    return apiClient.post(`/staff/borrows/${id}/cancel`)
  },

  sendOtp(id) {
    return apiClient.post(`/staff/borrows/${id}/send-otp`)
  },

  sendReturnOtp(id) {
    return apiClient.post(`/staff/borrows/${id}/send-return-otp`)
  },

  return(id, data) {
    return apiClient.post(`/staff/borrows/${id}/return`, data)
  },

  exportBorrows(filters = {}) {
    return apiClient.get('/staff/borrows/export', {
      params: filters,
      responseType: 'blob',
    })
  },
}
