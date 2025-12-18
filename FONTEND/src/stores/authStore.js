import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '../services/auth/authService'
import { useNotifications } from './notificationStore'
import router from '../router'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(JSON.parse(localStorage.getItem('user')) || null)
    const token = ref(localStorage.getItem('token') || null)

    const isAuthenticated = computed(() => !!token.value && !!user.value)
    const role = computed(() => user.value?.role)

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

    const login = async (email, password) => {
        const res = await authService.login(email, password)
        if (res.success) {
            token.value = res.token
            user.value = res.user
            persistAuth()

            try {
                const notifications = useNotifications()
                notifications.startPolling()
            } catch (error) {
                console.error('[Auth] Failed to start notification polling:', error)
            }

            return { success: true, role: user.value.role }
        }
        return res
    }

    const logout = async () => {
        try {
            await authService.logout()
        } catch (error) {
            console.warn('Logout request failed', error)
        } finally {
            try {
                const notifications = useNotifications()
                notifications.stopPolling()
                notifications.clear()
            } catch (error) {
                console.error('[Auth] Failed to stop notifications:', error)
            }

            user.value = null
            token.value = null
            persistAuth()

            if (router.currentRoute.value.name !== 'login') {
                router.push({ name: 'login' })
            }
        }
    }

    const verifyToken = async () => {
        if (!token.value) return false

        try {
            const userData = await authService.getProfile()
            if (!userData) throw new Error('Invalid user data')
            
            user.value = userData
            persistAuth()
            return true
        } catch (error) {
            console.error('Token verification error:', error)
            logout()
            return false
        }
    }

    const updateUser = (userData) => {
        if (user.value) {
            user.value = { ...user.value, ...userData }
            persistAuth()
        }
    }

    return {
        user,
        token,
        isAuthenticated,
        role,
        login,
        logout,
        verifyToken,
        updateUser
    }
})
