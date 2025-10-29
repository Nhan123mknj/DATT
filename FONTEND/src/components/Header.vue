<template>
  <header>
    <nav class="bg-white border-gray-200 px-2 lg:px-4 py-2.5 shadow-sm">
      <div class="flex flex-wrap justify-between items-center">
        <!-- Logo/Brand -->
        <div class="flex items-center">
          <div class="relative w-full max-w-xs">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <font-awesome-icon icon="search" class="text-gray-400 text-sm" />
            </span>
            <input
              type="text"
              placeholder="Tìm kiếm"
              class="w-full pl-10 pr-3 py-2 rounded-lg bg-gray-100 border border-gray-300 placeholder-gray-500 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
            />
          </div>
        </div>

        <!-- User Info and Logout -->
        <div class="flex items-center space-x-4">
          <!-- User Info -->
          <div class="flex items-center space-x-2">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center overflow-hidden shadow-lg"
            >
              <img
                :src="currentUser?.avatar_url"
                class="w-full h-full object-cover"
              />
            </div>

            <div class="hidden md:block">
              <p class="text-sm font-medium text-gray-800">
                {{ user?.name }}
              </p>
              <p class="text-xs text-gray-500">
                {{ user?.email }}
              </p>
            </div>
          </div>

          <button
            @click="handleLogout"
            class="text-gray-600 hover:text-red-600 hover:bg-red-50 px-3 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2"
            title="Đăng xuất"
          >
            <font-awesome-icon icon="sign-out-alt" class="text-sm" />
            <span class="hidden md:inline text-sm font-medium">Đăng xuất</span>
          </button>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import authService, { user } from "../services/authService.js";
const router = useRouter();

const currentUser = computed(() => user.value);

const handleLogout = () => {
  authService.logout();
  router.push({ name: "login" });
};
</script>
