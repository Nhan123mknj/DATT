import apiClient from '../api/apiClient'

export const authService = {
  async login(email, password) {
    try {
      const res = await apiClient.post('/auth/login', { email, password })
      if (res.data.status) {
        return {
          success: true,
          token: res.data.data.access_token,
          user: res.data.data.user
        }
      }
      return { success: false, error: res.data.message }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Login failed'
      }
    }
  },

  async logout() {
    return apiClient.post('/auth/logout')
  },

  async getProfile() {
    const { data } = await apiClient.get('/auth/user-profile')
    return data?.data ?? data
  }
}

export default authService
