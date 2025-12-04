import { ref } from 'vue';

const notifications = ref([
  {
    id: 1,
    title: 'Đơn mượn được duyệt',
    message: 'Yêu cầu mượn thiết bị #123 của bạn đã được duyệt.',
    time: '5 phút trước',
    read: false,
    type: 'success'
  },
  {
    id: 2,
    title: 'Nhắc nhở trả thiết bị',
    message: 'Thiết bị Laptop Dell XPS sẽ hết hạn mượn vào ngày mai.',
    time: '1 giờ trước',
    read: false,
    type: 'warning'
  },
  {
    id: 3,
    title: 'Hệ thống bảo trì',
    message: 'Hệ thống sẽ bảo trì vào lúc 00:00 ngày 01/12.',
    time: '1 ngày trước',
    read: true,
    type: 'info'
  }
]);

export const notificationService = {
  getNotifications() {
    return notifications;
  },
  
  markAsRead(id) {
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.read = true;
    }
  },

  markAllAsRead() {
    notifications.value.forEach(n => n.read = true);
  },

  getUnreadCount() {
    return notifications.value.filter(n => !n.read).length;
  }
};
