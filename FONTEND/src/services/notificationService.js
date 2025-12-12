import apiClient from './api/apiClient';

export default {
    /**
     * Get user's notifications (paginated)
     */
    async getNotifications(page = 1) {
        const { data } = await apiClient.get(`/notifications?page=${page}`);
        return data;
    },

    /**
     * Get unread notification count
     */
    async getUnreadCount() {
        const { data } = await apiClient.get('/notifications/unread-count');
        return data.count;
    },

    /**
     * Mark single notification as read
     */
    async markAsRead(id) {
        const { data } = await apiClient.post(`/notifications/${id}/mark-read`);
        return data;
    },

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        const { data } = await apiClient.post('/notifications/mark-all-read');
        return data;
    }
};
