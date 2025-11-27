import axios from 'axios'
import authService from '../auth/authService'
import router from '../../router'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL 

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
})

apiClient.interceptors.request.use((config) => {
  const token = authService.getToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  const isFormData = typeof FormData !== 'undefined' && config.data instanceof FormData
  if (!isFormData && !config.headers['Content-Type']) {
    config.headers['Content-Type'] = 'application/json'
  }
  return config
})

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      authService.logout()
      if (router.currentRoute.value.name !== 'login') {
        router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
      }
    }

    if (status === 403) {
      const errorMessage = error.response?.data?.message || 'Không có quyền truy cập'
      router.push({ name: 'forbidden', query: { message: errorMessage } })
    }

    return Promise.reject(error)
  }
)

export default apiClient
