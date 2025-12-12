import { ref, computed } from 'vue'
import apiClient from '../api/apiClient'
import { useNotifications } from '../../stores/notificationStore'

const user = ref(null)
const token = ref(localStorage.getItem('token') || null)
const isAuthenticatedState = computed(() => !!token.value && !!user.value)

const persistAuth = () => {
  if (token.value) {
    localStorage.setItem('token', token.value)
  } else {
    localStorage.removeItem('token')
  }

  if (user.value) {
    localStorage.setItem('user', JSON.stringify(user.value))
  } else {
    localStorage.removeItem('user')
  }
}

class AuthService {
 async login(email, password) {
  try {
    const res = await apiClient.post('/auth/login', { email, password });

    if (res.data.status) {
      const tokenData = res.data.data;

      token.value = tokenData.access_token;
      user.value = tokenData.user;
      persistAuth();

      try {
        const notifications = useNotifications();
        notifications.startPolling();
      } catch (error) {
        console.error('[Auth] Failed to start notification polling:', error);
      }

      return { success: true, role: user.value.role };
    } else {
      return { success: false, error: res.data.message };
    }
  } catch (error) {
    return { 
      success: false, 
      error: error.response?.data?.message || "Login failed" 
    };
  }
}


  async logout() {
    try {
      if (token.value) {
        await apiClient.post('/auth/logout')
      }
    } catch (error) {
      if (error.response?.status !== 401) {
        console.warn('Logout request failed', error)
      }
    } finally {
      try {
        const notifications = useNotifications();
        notifications.stopPolling();
        notifications.clear();
      } catch (error) {
        console.error('[Auth] Failed to stop notifications:', error);
      }

      user.value = null
      token.value = null
      persistAuth()

      if (typeof window !== 'undefined' && window.resetTokenVerification) {
        window.resetTokenVerification()
      }
    }
  }

  getCurrentUser() {
    return user.value
  }

  getUser() {
    return user.value
  }

  getToken() {
    return token.value
  }

  isAuthenticated() {
    return isAuthenticatedState.value
  }

  initAuth() {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user')

    if (storedToken && storedUser) {
      token.value = storedToken
      user.value = JSON.parse(storedUser)
    }
  }

  async verifyToken() {
    if (!token.value) return false

    try {
      const { data } = await apiClient.get('/auth/user-profile')
      const userData = data?.data ?? data

      if (!userData) {
        throw new Error('Invalid user payload from /auth/user-profile')
      }

      user.value = userData
      persistAuth()
      return true
    } catch (error) {
      console.error('Token verification error:', error)
      await this.logout()
      return false
    }
  }

  updateUser(userData) {
    if (user.value) {
      user.value = { ...user.value, ...userData }
      persistAuth()
    }
  }
}

const authService = new AuthService()
authService.initAuth()

export default authService
export { user, token, isAuthenticatedState as isAuthenticated }
