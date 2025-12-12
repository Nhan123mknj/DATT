import apiClient from '../api/apiClient'

export const reservationsService = {
    list(params = {}) {
        return apiClient.get('/staff/reservations', { params })
    },

    show(id) {
        return apiClient.get(`/staff/reservations/${id}`)
    },

    statistics() {
        return apiClient.get('/staff/reservations/statistics')
    },

    approve(id) {
        return apiClient.post(`/staff/reservations/${id}/approve`)
    },

    reject(id, payload) {
        return apiClient.post(`/staff/reservations/${id}/reject`, payload)
    },

    createBorrow(id) {
        return apiClient.post(`/staff/reservations/${id}/create-borrow`)
    },
    
}
