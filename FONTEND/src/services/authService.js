import { ref, computed } from 'vue'

// Reactive state
const user = ref(null)
const token = ref(localStorage.getItem('token') || null)
const isAuthenticated = computed(() => !!token.value && !!user.value)

// API base URL - adjust according to your backend
const API_BASE_URL = 'http://localhost:8000/api'

class AuthService {

  async login(email, password) {
    try {
      const response = await fetch(`${API_BASE_URL}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password })
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Đăng nhập thất bại')
      }

      const data = await response.json()
      

      token.value = data.token || data.access_token
      user.value = data.user
      

      localStorage.setItem('token', token.value)
      localStorage.setItem('user', JSON.stringify(user.value))
      
      return { success: true, role: user.value.role }
    } catch (error) {
      console.error('Login error:', error)
      return { success: false, error: error.message }
    }
  }

  // Logout method
  logout() {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    
    // Reset verification flag
    if (typeof window !== 'undefined' && window.resetTokenVerification) {
      window.resetTokenVerification()
    }
  }

  // Get current user
  getCurrentUser() {
    return user.value
  }

  // Get user (alias for getCurrentUser)
  getUser() {
    return user.value
  }

  // Get token
  getToken() {
    return token.value
  }

  // Check if user is authenticated
  isAuthenticated() {
    return isAuthenticated.value
  }

  // Initialize auth state from localStorage
  initAuth() {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user')
    
    if (storedToken && storedUser) {
      token.value = storedToken
      user.value = JSON.parse(storedUser)
    }
  }

  // Verify token with backend
  async verifyToken() {
    if (!token.value) return false
    
    try {
      const response = await fetch(`${API_BASE_URL}/auth/user-profile`, {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json',
        }
      })

      if (response.ok) {
        const userData = await response.json()
        user.value = userData
        localStorage.setItem('user', JSON.stringify(userData))
        return true
      } else {
        this.logout()
        return false
      }
    } catch (error) {
      console.error('Token verification error:', error)
      this.logout()
      return false
    }
  }
}

// Create singleton instance
const authService = new AuthService()

// Initialize auth state on module load
authService.initAuth()

export default authService
export { user, token, isAuthenticated }
