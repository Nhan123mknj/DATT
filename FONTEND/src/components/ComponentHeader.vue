<template>
  <header class="sticky top-0 z-50">
    <nav
      class="bg-white border-b border-gray-200 px-4 lg:px-6 py-2.5 shadow-sm"
    >
      <div
        class="flex flex-wrap justify-between items-center mx-auto max-w-screen-2xl"
      >
        <div class="flex items-center gap-8">
          <RouterLink
            v-if="currentUser?.role === 'borrower'"
            :to="{ name: 'borrower.dashboard' }"
            class="flex items-center gap-2 text-xl font-bold text-indigo-600"
          >
            <font-awesome-icon icon="laptop-code" />
            <span>DeviceManager</span>
          </RouterLink>

          <div class="relative hidden md:block w-64 lg:w-80">
            <span
              class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
            >
              <font-awesome-icon icon="search" class="text-gray-400 text-sm" />
            </span>
            <input
              type="text"
              placeholder="Tìm kiếm thiết bị..."
              class="w-full pl-10 pr-4 py-2 rounded-lg bg-gray-50 border-transparent focus:border-gray-200 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 text-sm"
            />
          </div>

          <!-- Borrower Navigation -->
          <div
            v-if="currentUser?.role === 'borrower'"
            class="hidden md:flex items-center space-x-1"
          >
            <RouterLink
              :to="{ name: 'borrower.dashboard' }"
              class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="
                isActive('borrower.dashboard')
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
              "
            >
              Tổng quan
            </RouterLink>
            <RouterLink
              :to="{ name: 'borrower.reservations' }"
              class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="
                isActive('borrower.reservations')
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
              "
            >
              Đặt trước
            </RouterLink>
            <RouterLink
              :to="{ name: 'borrower.borrows' }"
              class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="
                isActive('borrower.borrows')
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
              "
            >
              Phiếu mượn
            </RouterLink>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <!-- Notification Dropdown -->
          <NotificationDropdown />

          <!-- User Profile -->
          <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
            <div class="text-right hidden md:block">
              <p class="text-sm font-semibold text-gray-900">
                {{ currentUser?.name || "Người dùng" }}
              </p>
              <p class="text-xs text-gray-500 font-medium">
                {{ currentUser?.email || "Chưa xác định" }}
              </p>
            </div>

            <div class="relative group">
              <button class="flex items-center gap-2 focus:outline-none">
                <div
                  class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg border-2 border-white shadow-sm"
                >
                  <img
                    v-if="currentUser?.avatar_url"
                    :src="currentUser.avatar_url"
                    class="w-full h-full rounded-full object-cover"
                    alt="Avatar"
                  />
                  <span v-else>{{ getInitials(currentUser?.name) }}</span>
                </div>
              </button>

              <div
                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 transform origin-top-right z-50"
              >
                <div class="px-4 py-3 border-b border-gray-50 md:hidden">
                  <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ currentUser?.name }}
                  </p>
                  <p class="text-xs text-gray-500 truncate">
                    {{ currentUser?.email }}
                  </p>
                </div>

                <RouterLink
                  :to="{ name: getAccountRouteName() }"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600"
                >
                  <font-awesome-icon icon="user-cog" class="w-4 mr-2" />
                  Tài khoản
                </RouterLink>

                <button
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  <font-awesome-icon icon="sign-out-alt" class="w-4 mr-2" />
                  Đăng xuất
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
import authService from "../services/auth/authService.js";
import NotificationDropdown from "./common/NotificationDropdown.vue";
import { useUserAccount } from "../composables/useUserAccount.js";
import { useUserHelpers } from "../composables/useUserHelpers.js";
import { useRouter, useRoute } from "vue-router";

export default {
  name: "Header",
  components: {
    NotificationDropdown,
  },
  setup() {
    const { currentUser } = useUserAccount();
    const { getInitials } = useUserHelpers();
    const router = useRouter();
    const route = useRoute();

    const handleLogout = async () => {
      await authService.logout();
      router.push({ name: "login" });
    };

    const getAccountRouteName = () => {
      const role = currentUser.value?.role;
      const routeNames = {
        admin: "admin.account",
        staff: "staff.account",
        borrower: "borrower.account",
      };
      return routeNames[role] || "borrower.account";
    };

    const isActive = (routeName) => {
      return (
        route.name === routeName ||
        (route.path && route.path.startsWith(`/${routeName.split(".")[1]}`))
      );
    };

    return {
      currentUser,
      handleLogout,
      getInitials,
      getAccountRouteName,
      isActive,
    };
  },
};
</script>
