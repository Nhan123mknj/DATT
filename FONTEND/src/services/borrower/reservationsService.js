import apiClient from '../../api/apiClient'

const buildReservationFormData = (payload) => {
    const formData = new FormData()
    formData.append('reserved_from', payload.reserved_from)
    formData.append('reserved_until', payload.reserved_until)

    if (payload.notes) {
        formData.append('notes', payload.notes)
    }

    payload.devices.forEach((device, index) => {
        formData.append(`devices[${index}][device_unit_id]`, device.device_unit_id)
    })

    if (payload.commitment_file) {
        formData.append('commitment_file', payload.commitment_file)
    }

    return formData
}

export const reservationsService = {
    list(params = {}) {
        return apiClient.get('/borrower/reservations', { params })
    },

    show(id) {
        return apiClient.get(`/borrower/reservations/${id}`)
    },

    create(payload) {
        const data =
            payload instanceof FormData ? payload : buildReservationFormData(payload)
        return apiClient.post('/borrower/reservations', data, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })
    },

    cancel(id) {
        return apiClient.post(`/borrower/reservations/${id}/cancel`)
    },
}
