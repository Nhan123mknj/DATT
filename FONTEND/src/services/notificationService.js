import apiClient from './api/apiClient';

export default {
    async getNotifications(page = 1) {
        const { data } = await apiClient.get(`/notifications?page=${page}`);
        return data;
    },

    async getUnreadCount() {
        const { data } = await apiClient.get('/notifications/unread-count');
        return data.count;
    },

    async markAsRead(id) {
        const { data } = await apiClient.post(`/notifications/${id}/mark-read`);
        return data;
    },

    async markAllAsRead() {
        const { data } = await apiClient.post('/notifications/mark-all-read');
        return data;
    }
};
