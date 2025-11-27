import apiClient from '../api/apiClient'

const BASE_URL = '/admin/users'

export const usersService = {
  getAllUser(page = 1, perPage = 10, filters = {}) {
    return apiClient.get(BASE_URL, {
      params: {
        page,
        per_page: perPage,
        ...filters,
      },
    })
  },

  getUserById(id) {
    return apiClient.get(`${BASE_URL}/${id}`)
  },

  createUser(userData) {
    return apiClient.post(BASE_URL, userData)
  },

  updateUser(id, userData) {
    return apiClient.put(`${BASE_URL}/${id}`, userData)
  },

  deleteUser(id) {
    return apiClient.delete(`${BASE_URL}/${id}`)
  },

  resetPassword(id) {
    return apiClient.post(`${BASE_URL}/${id}/reset-password`)
  },

  toggleStatus(id) {
    return apiClient.patch(`${BASE_URL}/${id}/toogle-status`)
  },
}
