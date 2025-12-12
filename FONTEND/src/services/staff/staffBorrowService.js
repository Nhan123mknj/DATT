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

  /**
   * Process return with signatures and photos
   * @param {number} id - Borrow ID
   * @param {Object} data - Return data with notes
   */
  return(id, data) {
    return apiClient.post(`/staff/borrows/${id}/return`, data)
  },

  /**
   * Process return with multipart form data (signatures, photos)
   * @param {number} id - Borrow ID
   * @param {FormData} formData - Multipart form data
   */
  processReturn(id, formData) {
    return apiClient.post(`/staff/borrows/${id}/return`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}
