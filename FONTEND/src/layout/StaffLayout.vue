<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo and Navigation Links -->
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-gray-800">Staff Portal</h1>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <RouterLink
                :to="{ name: 'staff.dashboard' }"
                class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-indigo-500"
              >
                Dashboard
              </RouterLink>
              <!-- Add more navigation links as needed -->
            </div>
          </div>

          <div class="flex items-center">
            <div class="ml-3 relative">
              <div class="flex items-center space-x-4">
                <img
                  v-if="user?.avatar_url"
                  :src="user.avatar_url"
                  alt="User Avatar"
                  class="h-8 w-8 rounded-full object-cover"
                />
                <span class="text-gray-700">{{ user?.name }}</span>
                <button
                  @click="handleLogout"
                  class="text-gray-700 hover:text-gray-900 font-medium"
                >
                  Đăng xuất
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import { RouterLink } from "vue-router";
import authService from "../services/authService";

export default {
  name: "StaffLayout",
  components: {
    RouterLink,
  },
  data() {
    return {
      user: null,
    };
  },
  created() {
    this.user = authService.getUser();
  },
  methods: {
    async handleLogout() {
      try {
        await authService.logout();
        this.$router.push({ name: "login" });
      } catch (error) {
        console.error("Logout failed:", error);
      }
    },
  },
};
</script>
