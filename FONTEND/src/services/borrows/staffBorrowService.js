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
        return apiClient.post(`/staff/borrows/${id}/approve`)
    },

    reject(id, data) {
        return apiClient.post(`/staff/borrows/${id}/reject`, data)
    },

    processReturn(id, data) {
        return apiClient.post(`/staff/borrows/${id}/return`, data)
    },

}
