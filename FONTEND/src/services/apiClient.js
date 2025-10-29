import axios from 'axios'
import authService from './authService'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// Thêm token tự động vào headers
apiClient.interceptors.request.use((config) => {
  const token = authService.getToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Xử lý lỗi response
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {

      authService.logout()
      window.location.href = '/login'
    }
    if (error.response?.status === 403) {
     const errorMessage = error.response.data.message || 'Không có quyền truy cập'
      router.push({ name: 'forbidden', params: { message: errorMessage } })
    }
    return Promise.reject(error)
  }
)

export default apiClient  
