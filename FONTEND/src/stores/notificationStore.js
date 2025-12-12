import { ref, computed } from 'vue';
import notificationService from '../services/notificationService';
import { useToast } from 'vue-toastification';

// Shared state (singleton pattern)
const notifications = ref([]);
const toast = useToast();
let pollingInterval = null;
let isPolling = false;

export function useNotifications() {
    const unreadCount = computed(() => 
        notifications.value.filter(n => !n.read).length
    );

    /**
     * Fetch notifications from API
     */
    async function fetchNotifications() {
        try {
            const data = await notificationService.getNotifications();
            const newNotifications = data.data || [];
            
            if (notifications.value.length > 0) {
                newNotifications.forEach(newNotif => {
                    const exists = notifications.value.find(n => n.id === newNotif.id);
                    if (!exists && !newNotif.read) {
                        const toastType = getToastType(newNotif.type);
                        toast[toastType](newNotif.message, {
                            timeout: 5000,
                            closeOnClick: true,
                            pauseOnHover: true
                        });
                    }
                });
            }
            
            notifications.value = newNotifications;
            console.log('[Notifications] Fetched:', newNotifications.length);
        } catch (error) {
            console.error('[Notifications] Fetch failed:', error);
        }
    }

    /**
     * Get toast type based on notification type
     */
    function getToastType(type) {
        const typeMap = {
            'App\\Notifications\\BorrowNotification': 'info',
            'borrow.created': 'success',
            'reservation.approved': 'success',
            'reservation.rejected': 'error',
            'reservation.cancelled': 'warning'
        };
        return typeMap[type] || 'info';
    }

    /**
     * Start polling for notifications
     */
    function startPolling() {
        if (isPolling) {
            console.log('[Notifications] Already polling, skipping');
            return;
        }

        console.log('[Notifications] Starting polling');
        isPolling = true;
        
        fetchNotifications();
        
        pollingInterval = setInterval(fetchNotifications, 30000);
    }

    /**
     * Stop polling
     */
    function stopPolling() {
        console.log('[Notifications] Stopping polling');
        isPolling = false;
        
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    /**
     * Mark notification as read
     */
    async function markAsRead(id) {
        try {
            await notificationService.markAsRead(id);
            const notif = notifications.value.find(n => n.id === id);
            if (notif) {
                notif.read = true;
            }
        } catch (error) {
            console.error('[Notifications] Mark read failed:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async function markAllAsRead() {
        try {
            await notificationService.markAllAsRead();
            notifications.value.forEach(n => n.read = true);
        } catch (error) {
            console.error('[Notifications] Mark all read failed:', error);
        }
    }

    /**
     * Clear all notifications
     */
    function clear() {
        notifications.value = [];
    }

    return {
        notifications,
        unreadCount,
        fetchNotifications,
        startPolling,
        stopPolling,
        markAsRead,
        markAllAsRead,
        clear
    };
}

// Alias export for backward compatibility
export const useNotificationStore = useNotifications;
