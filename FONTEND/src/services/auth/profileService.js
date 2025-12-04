import apiClient from '../api/apiClient'

export const profileService = {
    // Get user profile
    getProfile() {
        return apiClient.get('/auth/user-profile')
    },

    // Update profile
    updateProfile(data) {
        return apiClient.post('/auth/user-profile', data)
    },

    // Change password
    changePassword(data) {
        return apiClient.post('/auth/change-password', data)
    },

    // Upload avatar
    uploadAvatar(file) {
        const formData = new FormData()
        formData.append('avatar', file)

        return apiClient.post('/auth/upload-avatar', formData)
    },
}
