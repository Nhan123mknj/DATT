<template>
  <div class="relative group" ref="dropdownRef">
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-full focus:outline-none transition-colors"
    >
      <font-awesome-icon icon="bell" class="text-xl" />
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full"
      >
        {{ unreadCount }}
      </span>
    </button>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50 origin-top-right"
    >
      <div
        class="px-4 py-3 border-b border-gray-50 flex justify-between items-center"
      >
        <h3 class="text-sm font-semibold text-gray-900">Thông báo</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
        >
          Đánh dấu tất cả đã đọc
        </button>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <div
          v-if="notifications.length === 0"
          class="px-4 py-6 text-center text-gray-500 text-sm"
        >
          Không có thông báo nào
        </div>
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer border-b border-gray-50 last:border-0"
          :class="{ 'bg-blue-50/50': !notification.read }"
          @click="markAsRead(notification)"
        >
          <div class="flex gap-3">
            <div class="shrink-0 mt-1">
              <span
                class="w-2 h-2 rounded-full inline-block"
                :class="{
                  'bg-blue-500': !notification.read,
                  'bg-gray-300': notification.read,
                }"
              ></span>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900 mb-1">
                {{ notification.title }}
              </p>
              <p class="text-xs text-gray-600 mb-1 line-clamp-2">
                {{ notification.message }}
              </p>
              <p class="text-[10px] text-gray-400">
                {{ notification.time }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="px-4 py-2 border-t border-gray-50 text-center">
        <button
          class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
        >
          Xem tất cả
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { notificationService } from "../../services/notificationService";

export default {
  name: "NotificationDropdown",
  data() {
    return {
      isOpen: false,
    };
  },
  computed: {
    notifications() {
      return notificationService.getNotifications().value;
    },
    unreadCount() {
      return notificationService.getUnreadCount();
    },
  },
  mounted() {
    document.addEventListener("click", this.closeDropdown);
  },
  unmounted() {
    document.removeEventListener("click", this.closeDropdown);
  },
  methods: {
    toggleDropdown() {
      this.isOpen = !this.isOpen;
    },
    closeDropdown(e) {
      if (
        this.$refs.dropdownRef &&
        !this.$refs.dropdownRef.contains(e.target)
      ) {
        this.isOpen = false;
      }
    },
    markAsRead(notification) {
      if (!notification.read) {
        notificationService.markAsRead(notification.id);
      }
    },
    markAllAsRead() {
      notificationService.markAllAsRead();
    },
  },
};
</script>
