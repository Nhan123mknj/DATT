<template>
  <aside class="bg-white text-black w-64 min-h-screen">
    <div class="px-4 py-4 text-lg text-center font-semibold border-gray-800">
      Hệ thống quản lý thiết bị
    </div>
    <nav class="py-2">
      <ul>
        <li v-for="item in menuItems" :key="item.id" class="py-1 px-2">
          <span
            v-if="item.children && item.children.length"
            @click="toggle(item.id)"
            class="flex items-center justify-between w-full px-3 py-2 cursor-pointer select-none text-black"
          >
            <span class="font-normal uppercase text-gray-400">{{
              item.label
            }}</span>
            <svg
              :class="{ 'rotate-90': isOpen(item.id) }"
              class="h-4 w-4 transition-transform duration-200 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </span>

          <RouterLink
            v-else-if="item.route"
            :to="item.route"
            class="flex items-center gap-2 py-2 px-3 rounded-lg transition-colors duration-200 text-gray-700 hover:bg-blue-100 hover:text-blue-700 focus:bg-blue-200 focus:text-blue-800 active:bg-blue-300"
          >
            <span v-if="item.icon" :class="item.icon"></span>
            <span class="font-medium">{{ item.label }}</span>
          </RouterLink>
          <div v-else class="px-3 py-2 text-gray-400">
            <span v-if="item.icon" :class="item.icon"></span>
            <span>{{ item.label }}</span>
          </div>

          <transition name="collapse">
            <ul
              v-if="item.children && item.children.length && isOpen(item.id)"
              class="ml-2 border-gray-800"
            >
              <li v-for="child in item.children" :key="child.id">
                <RouterLink
                  v-if="child.route"
                  :to="child.route"
                  class="flex items-center gap-2 py-2 px-3 rounded-lg transition-colors duration-200 text-gray-700 hover:bg-blue-100 hover:text-blue-700 focus:bg-blue-200 focus:text-blue-800 active:bg-blue-300"
                >
                  <span v-if="child.icon" :class="child.icon"></span>
                  <span>{{ child.label }}</span>
                </RouterLink>
                <div v-else class="px-3 py-2 text-gray-400">
                  {{ child.label }}
                </div>
              </li>
            </ul>
          </transition>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import authService from "../services/authService";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

const menuItems = ref([]);
const openMap = reactive({});

const fetchMenu = async () => {
  try {
    const token = authService.getToken();
    const res = await fetch(`${API_BASE_URL}api/menus`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    });
    if (!res.ok) throw new Error("Failed to load menu");
    const data = await res.json();
    menuItems.value = data.items || [];
  } catch (e) {
    console.error("Sidebar menu error:", e);
    menuItems.value = [];
  }
};

const toggle = (id) => (openMap[id] = !openMap[id]);
const isOpen = (id) => !!openMap[id];

onMounted(fetchMenu);
</script>

<style scoped>
.collapse-enter-active,
.collapse-leave-active {
  transition: max-height 0.2s ease;
}
.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
}
.collapse-enter-to,
.collapse-leave-from {
  max-height: 500px;
}
</style>
