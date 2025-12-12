<template>
  <div class="notification-bell-wrapper" v-click-outside="closeDropdown">
    <button
      @click="toggleDropdown"
      class="notification-bell-button"
      :class="{ 'has-notifications': unreadCount > 0 }"
    >
      <span class="icon">🔔</span>
      <span v-if="unreadCount > 0" class="badge">
        {{ unreadCount > 99 ? "99+" : unreadCount }}
      </span>
    </button>

    <transition name="dropdown-fade">
      <div v-if="showDropdown" class="notification-dropdown">
        <div class="dropdown-header">
          <h3>Thông Báo</h3>
          <button
            v-if="unreadCount > 0"
            @click="handleMarkAllRead"
            class="mark-all-btn"
          >
            Đánh dấu đã đọc
          </button>
        </div>

        <div class="notifications-list">
          <div
            v-for="notif in limitedNotifications"
            :key="notif.id || notif.timestamp"
            :class="['notification-item', { unread: !notif.read }]"
            @click="handleNotificationClick(notif)"
          >
            <div class="notification-content">
              <p class="message">{{ notif.message }}</p>
              <span class="time">{{
                formatTime(notif.timestamp || notif.created_at)
              }}</span>
            </div>
            <div v-if="!notif.read" class="unread-indicator"></div>
          </div>

          <div v-if="notifications.length === 0" class="empty-state">
            <span class="empty-icon">📭</span>
            <p>Không có thông báo mới</p>
          </div>
        </div>

        <div v-if="notifications.length > 5" class="dropdown-footer">
          <router-link to="/notifications" class="view-all-link">
            Xem tất cả thông báo
          </router-link>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useNotificationStore } from "@/stores/notificationStore";

export default {
  name: "NotificationBell",

  directives: {
    clickOutside: {
      beforeMount(el, binding) {
        el.clickOutsideEvent = (event) => {
          if (!(el === event.target || el.contains(event.target))) {
            binding.value(event);
          }
        };
        document.addEventListener("click", el.clickOutsideEvent);
      },
      unmounted(el) {
        document.removeEventListener("click", el.clickOutsideEvent);
      },
    },
  },

  setup() {
    const router = useRouter();
    const notifStore = useNotificationStore();
    const showDropdown = ref(false);

    const notifications = computed(() => notifStore.notifications);
    const unreadCount = computed(() => notifStore.unreadCount);
    const limitedNotifications = computed(() =>
      notifications.value.slice(0, 5)
    );

    const toggleDropdown = () => {
      showDropdown.value = !showDropdown.value;
    };

    const closeDropdown = () => {
      showDropdown.value = false;
    };

    const handleMarkAllRead = () => {
      notifStore.markAllAsRead();
    };

    const handleNotificationClick = (notif) => {
      notifStore.markAsRead(notif.id);
      closeDropdown();

      if (notif.borrow_id) {
        const role = localStorage.getItem("userRole") || "staff";
        router.push(`/${role}/borrows`);
      }
    };

    const formatTime = (timestamp) => {
      if (!timestamp) return "";

      const date = new Date(timestamp);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);

      if (diffMins < 1) return "Vừa xong";
      if (diffMins < 60) return `${diffMins} phút trước`;

      const diffHours = Math.floor(diffMins / 60);
      if (diffHours < 24) return `${diffHours} giờ trước`;

      const diffDays = Math.floor(diffHours / 24);
      if (diffDays < 7) return `${diffDays} ngày trước`;

      return date.toLocaleDateString("vi-VN");
    };

    return {
      notifications,
      unreadCount,
      limitedNotifications,
      showDropdown,
      toggleDropdown,
      closeDropdown,
      handleMarkAllRead,
      handleNotificationClick,
      formatTime,
    };
  },
};
</script>

<style scoped>
.notification-bell-wrapper {
  position: relative;
}

.notification-bell-button {
  position: relative;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-bell-button:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.notification-bell-button.has-notifications .icon {
  animation: ring 2s ease-in-out infinite;
}

@keyframes ring {
  0%,
  100% {
    transform: rotate(0deg);
  }
  10%,
  30% {
    transform: rotate(-10deg);
  }
  20%,
  40% {
    transform: rotate(10deg);
  }
}

.icon {
  font-size: 20px;
  display: block;
}

.badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background-color: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 5px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.notification-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 360px;
  max-height: 480px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.dropdown-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.mark-all-btn {
  background: none;
  border: none;
  color: #6366f1;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.mark-all-btn:hover {
  background-color: #eef2ff;
}

.notifications-list {
  flex: 1;
  overflow-y: auto;
  max-height: 360px;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 16px;
  cursor: pointer;
  transition: background-color 0.2s;
  border-bottom: 1px solid #f3f4f6;
}

.notification-item:hover {
  background-color: #f9fafb;
}

.notification-item.unread {
  background-color: #eff6ff;
}

.notification-item.unread:hover {
  background-color: #dbeafe;
}

.notification-content {
  flex: 1;
}

.message {
  margin: 0 0 4px 0;
  font-size: 14px;
  color: #374151;
  line-height: 1.4;
}

.notification-item.unread .message {
  font-weight: 500;
}

.time {
  font-size: 12px;
  color: #9ca3af;
}

.unread-indicator {
  width: 8px;
  height: 8px;
  background-color: #3b82f6;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 6px;
}

.empty-state {
  text-align: center;
  padding: 48px 24px;
  color: #6b7280;
}

.empty-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.dropdown-footer {
  border-top: 1px solid #e5e7eb;
  padding: 12px 16px;
  text-align: center;
  background-color: #f9fafb;
}

.view-all-link {
  color: #6366f1;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.2s;
}

.view-all-link:hover {
  color: #4f46e5;
  text-decoration: underline;
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
