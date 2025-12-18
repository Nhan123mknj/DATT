import axios from 'axios'

import router from '../../router'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL 

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
})

import { useAuthStore } from '../../stores/authStore'

apiClient.interceptors.request.use((config) => {
  const authStore = useAuthStore()
  const token = authStore.token
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
    const authStore = useAuthStore()

    if (status === 401) {
      if (!error.config.url.includes('/auth/logout')) {
        authStore.logout()
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
