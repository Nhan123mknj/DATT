<template>
  <aside class="bg-white text-black w-64 min-h-screen border-r border-gray-100">
    <div
      class="px-4 py-4 text-lg text-center font-semibold border-b border-gray-100"
    >
      Hệ thống quản lý thiết bị
    </div>
    <nav class="py-3">
      <ul>
        <li v-for="item in menuItems" :key="item.id" class="py-1 px-2">
          <button
            v-if="item.children && item.children.length"
            @click="toggle(item.id)"
            class="flex items-center justify-between w-full px-3 py-2 cursor-pointer select-none text-left rounded-lg hover:bg-gray-100"
          >
            <span class="font-semibold text-gray-700">{{ item.label }}</span>
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
          </button>

          <RouterLink
            v-else-if="item.url"
            :to="item.url"
            class="flex items-center gap-2 py-2 px-3 rounded-lg transition-colors duration-200"
            :class="linkClasses(item.url)"
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
              class="ml-2 border-l border-gray-100 pl-2"
            >
              <li v-for="child in item.children" :key="child.id">
                <RouterLink
                  v-if="child.url"
                  :to="child.url"
                  class="flex items-center gap-2 py-2 px-3 rounded-lg transition-colors duration-200"
                  :class="linkClasses(child.url)"
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
import { onMounted, reactive, ref, watch } from "vue";
import { RouterLink, useRoute } from "vue-router";
import apiClient from "../services/api/apiClient";

const menuItems = ref([]);
const openMap = reactive({});
const route = useRoute();

const normalizeRoute = (path) => {
  if (!path) return null;
  return path;
};

const transformItems = (items) =>
  items
    .map((item) => ({
      ...item,
      url: normalizeRoute(item.url),
      children: item.children ? transformItems(item.children) : [],
    }))
    .filter((item) => {
      return item.url || item.children?.length;
    });

const fetchMenu = async () => {
  try {
    const response = await apiClient.get("/menus/main");

    const items = response.data?.data || [];

    if (!Array.isArray(items) || items.length === 0) {
      menuItems.value = [];
      return;
    }

    // Get root items (parent_id is null or undefined)
    const rootItems = items.filter((item) => !item.parent_id);

    // Build tree structure
    const buildTree = (rootItems) => {
      return rootItems.map((item) => {
        const children = buildChildren(item.id, items);
        return {
          ...item,
          url: normalizeRoute(item.url),
          children: children,
        };
      });
    };

    const buildChildren = (parentId, allItems) => {
      return allItems
        .filter((item) => item.parent_id === parentId)
        .map((item) => ({
          ...item,
          url: normalizeRoute(item.url),
          children: buildChildren(item.id, allItems),
        }));
    };

    const tree = buildTree(rootItems);

    menuItems.value = transformItems(tree);

    expandActiveParents();
  } catch (e) {
    console.error("❌ Sidebar menu error:", e);
    menuItems.value = [];
  }
};

const toggle = (id) => {
  openMap[id] = !openMap[id];
};

const isOpen = (id) => !!openMap[id];

const isActivePath = (path) => {
  if (!path) return false;
  return route.path.startsWith(path);
};

const linkClasses = (path) => {
  const active = isActivePath(path);
  return active
    ? "bg-indigo-50 text-indigo-600 font-semibold"
    : "text-gray-700 hover:bg-gray-100 hover:text-indigo-600";
};

const expandActiveParents = () => {
  menuItems.value.forEach((item) => {
    if (item.children?.some((child) => isActivePath(child.url))) {
      openMap[item.id] = true;
    }
  });
};

watch(
  () => route.path,
  () => {
    expandActiveParents();
  }
);

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
