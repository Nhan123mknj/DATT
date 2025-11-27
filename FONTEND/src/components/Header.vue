<template>
  <header>
    <nav class="bg-white border-gray-200 px-2 lg:px-4 py-2.5 shadow-sm">
      <div class="flex flex-wrap justify-between items-center">
        <div class="flex items-center">
          <div class="relative w-full max-w-xs">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <font-awesome-icon icon="search" class="text-gray-400 text-sm" />
            </span>
            <input
              type="text"
              placeholder="Tìm kiếm"
              class="w-full pl-10 pr-3 py-2 rounded-lg bg-gray-100 border border-gray-300 placeholder-gray-500 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
              disabled
            />
          </div>
        </div>

        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden shadow-lg bg-indigo-50 text-indigo-600 font-semibold"
            >
              <img
                id="avatarButton"
                type="button"
                data-dropdown-toggle="userDropdown"
                data-dropdown-placement="bottom-start"
                class="w-10 h-10 rounded-full cursor-pointer"
                v-if="currentUser?.avatar_url"
                :src="currentUser.avatar_url"
                alt="Rounded avatar"
              />
              <div
                id="userDropdown"
                class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44"
              >
                <div
                  class="px-4 py-3 border-b border-default-medium text-sm text-heading"
                >
                  <div class="font-medium">Bonnie Green</div>
                  <div class="truncate">name@flowbite.com</div>
                </div>
                <ul
                  class="p-2 text-sm text-body font-medium"
                  aria-labelledby="avatarButton"
                >
                  <li>
                    <a
                      href="#"
                      class="block w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-md"
                      >Dashboard</a
                    >
                  </li>
                  <li>
                    <a
                      href="#"
                      class="block w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-md"
                      >Settings</a
                    >
                  </li>
                  <li>
                    <a
                      href="#"
                      class="block w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-md"
                      >Earnings</a
                    >
                  </li>
                  <li>
                    <a
                      href="#"
                      class="block w-full p-2 hover:bg-neutral-tertiary-medium text-fg-danger rounded-md"
                      >Sign out</a
                    >
                  </li>
                </ul>
              </div>
              <!-- <span v-else>{{ initials }}</span> -->
            </div>

            <div class="hidden md:block">
              <p class="text-sm font-medium text-gray-800">
                {{ currentUser?.name || "Người dùng" }}
              </p>
              <p class="text-xs text-gray-500">
                {{ currentUser?.email || "Chưa xác định" }}
              </p>
            </div>
          </div>

          <div
            class="flex items-center space-x-2 border-l border-gray-200 pl-4"
          >
            <router-link
              :to="{ name: getAccountRouteName() }"
              class="text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2"
              title="Tài khoản"
            >
              <font-awesome-icon icon="user-cog" class="text-sm" />
              <span class="hidden md:inline text-sm font-medium"
                >Tài khoản</span
              >
            </router-link>

            <button
              @click="handleLogout"
              class="text-gray-600 hover:text-red-600 hover:bg-red-50 px-3 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2"
              title="Đăng xuất"
            >
              <font-awesome-icon icon="sign-out-alt" class="text-sm" />
              <span class="hidden md:inline text-sm font-medium"
                >Đăng xuất</span
              >
            </button>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import authService, { user as authUser } from "../services/auth/authService.js";

const router = useRouter();

const currentUser = computed(() => authUser.value);

const getAccountRouteName = () => {
  const role = currentUser.value?.role;
  const routeNames = {
    admin: "admin.account",
    staff: "staff.account",
    borrower: "borrower.account",
  };
  return routeNames[role] || "borrower.account";
};

const handleLogout = async () => {
  await authService.logout();
  router.push({ name: "login" });
};
</script>
