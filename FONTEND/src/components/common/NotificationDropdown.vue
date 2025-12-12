<template>
  <div class="relative" ref="dropdownRef">
    <button
      @click="isOpen = !isOpen"
      class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-full"
    >
      <font-awesome-icon icon="bell" class="text-xl" />

      <span
        v-if="notifStore.unreadCount > 0"
        class="absolute top-0 right-0 px-2 py-1 text-xs font-bold text-red-100 bg-red-600 rounded-full"
      >
        {{ notifStore.unreadCount }}
      </span>
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50"
    >
      <div class="px-4 py-3 border-b flex justify-between items-center">
        <h3 class="text-sm font-semibold">
          Thông báo ({{ notificationsList.length }})
        </h3>

        <button
          v-if="notifStore.unreadCount > 0"
          @click="notifStore.markAllAsRead()"
          class="text-xs text-indigo-600 hover:text-indigo-800"
        >
          Đánh dấu tất cả
        </button>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <div
          v-if="notificationsList.length === 0"
          class="px-4 py-6 text-center text-gray-500 text-sm"
        >
          Không có thông báo
        </div>

        <div
          v-for="notif in notificationsList"
          :key="notif.id"
          class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
          :class="{ 'bg-blue-50': !notif.read }"
          @click="handleNotificationClick(notif)"
        >
          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div class="shrink-0 mt-1">
              <div
                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center"
              >
                <svg
                  class="w-4 h-4 text-blue-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                  />
                </svg>
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 mb-1">
                {{ notif.message || "Thông báo mới" }}
              </p>

              <!-- Metadata -->
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <span>{{
                  formatTime(notif.timestamp || notif.created_at)
                }}</span>
                <span
                  v-if="!notif.read"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  Mới
                </span>
              </div>
            </div>

            <!-- Unread indicator -->
            <div v-if="!notif.read" class="shrink-0">
              <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useNotifications } from "../../stores/notificationStore";
import authService from "../../services/auth/authService";

const router = useRouter();
const notifStore = useNotifications();
const notificationsList = notifStore.notifications;
const isOpen = ref(false);
const dropdownRef = ref(null);

const formatTime = (timestamp) => {
  if (!timestamp) return "";
  const date = new Date(timestamp);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return "Vừa xong";
  if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
  return `${Math.floor(diff / 86400)} ngày trước`;
};

const handleNotificationClick = async (notif) => {
  await notifStore.markAsRead(notif.id);
  isOpen.value = false;

  const data = notif.data || {};
  const user = authService.getUser();

  if (data.reservation_id) {
    const role = user?.role;
    if (role === "staff" || role === "admin") {
      router.push({
        name: "staff.reservations",
        query: { id: data.reservation_id },
      });
    } else {
      router.push({
        name: "borrower.reservations",
        query: { id: data.reservation_id },
      });
    }
  } else if (data.borrow_id) {
    const role = user?.role;
    if (role === "staff" || role === "admin") {
      router.push({
        name: "staff.borrows",
        query: { id: data.borrow_id },
      });
    } else {
      router.push({
        name: "borrower.borrows",
        query: { id: data.borrow_id },
      });
    }
  }
};

const closeDropdown = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener("click", closeDropdown);
});
</script>
