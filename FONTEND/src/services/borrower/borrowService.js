import apiClient from '../api/apiClient'

export const borrowService = {
  list(params = {}) {
    return apiClient.get('/borrower/borrows', { params })
  },

  show(id) {
    return apiClient.get(`/borrower/borrows/${id}`)
  },
}

