<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-indigo-600">DeviceManager</h1>
            </div>

            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <RouterLink
                :to="{ name: 'borrower.dashboard' }"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300"
                exact-active-class="border-indigo-500 text-gray-900"
              >
                Tổng quan
              </RouterLink>

              <RouterLink
                :to="{ name: 'borrower.reservations' }"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300"
                exact-active-class="border-indigo-500 text-gray-900"
              >
                Đặt trước
              </RouterLink>

              <RouterLink
                :to="{ name: 'borrower.borrows' }"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300"
                exact-active-class="border-indigo-500 text-gray-900"
              >
                Phiếu mượn
              </RouterLink>
              <RouterLink
                :to="{ name: 'borrower.returns' }"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300"
                exact-active-class="border-indigo-500 text-gray-900"
              >
                Phiếu trả
              </RouterLink>
            </div>
          </div>

          <div class="flex items-center">
            <NotificationDropdown />

            <div class="ml-3 relative">
              <div class="flex items-center space-x-4">
                <img
                  v-if="currentUser?.avatar_url"
                  :src="currentUser.avatar_url"
                  alt="User Avatar"
                  class="h-8 w-8 rounded-full object-cover"
                />
                <span class="text-gray-700 hidden md:block">{{
                  currentUser?.name
                }}</span>
                <button
                  @click="handleLogout"
                  class="text-gray-700 hover:text-gray-900 font-medium text-sm"
                >
                  Đăng xuất
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import { RouterLink } from "vue-router";
import NotificationDropdown from "../components/common/NotificationDropdown.vue";
import { useAuthStore } from "../stores/authStore";

const router = useRouter();
const authStore = useAuthStore();

const currentUser = computed(() => authStore.user);

const handleLogout = async () => {
  try {
    await authStore.logout();
    router.push({ name: "login" });
  } catch (error) {
    console.error("Logout failed:", error);
  }
};
</script>
<style scoped></style>
