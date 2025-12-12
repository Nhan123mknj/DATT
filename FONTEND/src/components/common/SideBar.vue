<template>
  <aside
    class="bg-white text-gray-800 w-64 min-h-screen border-r border-gray-200 flex flex-col transition-all duration-300"
  >
    <div
      class="h-16 flex items-center justify-center border-b border-gray-100 px-4"
    >
      <RouterLink
        to="/"
        class="flex items-center gap-2 text-xl font-bold text-indigo-600"
      >
        <font-awesome-icon icon="laptop-code" />
        <span>DeviceManager</span>
      </RouterLink>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 custom-scrollbar">
      <ul class="space-y-1">
        <li v-for="item in menuItems" :key="item.id">
          <!-- Parent Item with Children -->
          <button
            v-if="item.children && item.children.length"
            @click="toggle(item.id)"
            class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 group"
            :class="
              isOpen(item.id)
                ? 'bg-gray-50 text-indigo-600'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            "
          >
            <div class="flex items-center gap-3">
              <span
                v-if="item.icon"
                :class="item.icon"
                class="w-5 text-center"
              ></span>
              <span>{{ item.label }}</span>
            </div>
            <svg
              :class="{ 'rotate-90': isOpen(item.id) }"
              class="w-4 h-4 transition-transform duration-200 text-gray-400 group-hover:text-gray-500"
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

          <!-- Single Link Item -->
          <RouterLink
            v-else-if="item.url"
            :to="item.url"
            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200"
            :class="linkClasses(item.url)"
          >
            <span
              v-if="item.icon"
              :class="item.icon"
              class="w-5 text-center"
            ></span>
            <span>{{ item.label }}</span>
          </RouterLink>

          <!-- Non-link Item -->
          <div
            v-else
            class="px-3 py-2.5 text-sm font-medium text-gray-400 cursor-default"
          >
            <div class="flex items-center gap-3">
              <span
                v-if="item.icon"
                :class="item.icon"
                class="w-5 text-center"
              ></span>
              <span>{{ item.label }}</span>
            </div>
          </div>

          <!-- Children Submenu -->
          <transition name="collapse">
            <ul
              v-if="item.children && item.children.length && isOpen(item.id)"
              class="mt-1 ml-4 pl-2 border-l border-gray-100 space-y-1"
            >
              <li v-for="child in item.children" :key="child.id">
                <RouterLink
                  v-if="child.url"
                  :to="child.url"
                  class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors duration-200"
                  :class="linkClasses(child.url)"
                >
                  <span
                    v-if="child.icon"
                    :class="child.icon"
                    class="w-4 text-center text-xs"
                  ></span>
                  <span>{{ child.label }}</span>
                </RouterLink>
                <div v-else class="px-3 py-2 text-sm text-gray-400">
                  {{ child.label }}
                </div>
              </li>
            </ul>
          </transition>
        </li>
      </ul>
    </nav>

    <div class="p-4 border-t border-gray-100">
      <div class="text-xs text-center text-gray-400">
        &copy; 2024 Device Manager
      </div>
    </div>
  </aside>
</template>

<script>
import { RouterLink } from "vue-router";
import apiClient from "../../services/api/apiClient";
import authService from "../../services/auth/authService";

export default {
  name: "SideBar",
  data() {
    return {
      menuItems: [],
      openMap: {},
    };
  },
  watch: {
    "$route.path"() {
      this.expandActiveParents();
    },
  },
  mounted() {
    this.fetchMenu();
  },
  methods: {
    normalizeRoute(path) {
      if (!path) return null;
      return path;
    },
    transformItems(items) {
      return items
        .map((item) => ({
          ...item,
          url: this.normalizeRoute(item.url),
          children: item.children ? this.transformItems(item.children) : [],
        }))
        .filter((item) => {
          return item.url || item.children?.length;
        });
    },
    async fetchMenu() {
      try {
        const response = await apiClient.get("/menus/main");
        const items = response.data?.data || [];

        if (!Array.isArray(items) || items.length === 0) {
          this.menuItems = [];
          return;
        }

        const rootItems = items.filter((item) => !item.parent_id);

        const buildChildren = (parentId, allItems) => {
          return allItems
            .filter((item) => item.parent_id === parentId)
            .map((item) => ({
              ...item,
              url: this.normalizeRoute(item.url),
              children: buildChildren(item.id, allItems),
            }));
        };

        const buildTree = (rootItems) => {
          return rootItems.map((item) => {
            const children = buildChildren(item.id, items);
            return {
              ...item,
              url: this.normalizeRoute(item.url),
              children: children,
            };
          });
        };

        const tree = buildTree(rootItems);
        let finalItems = this.transformItems(tree);

        // Add Admin Menu Management link if admin
        const currentUser = authService.getUser();
        if (currentUser?.role === "admin") {
          const exists = finalItems.find((i) => i.url === "/admin/menus");
          if (!exists) {
            finalItems.push({
              id: "admin-menu-management",
              label: "Quản lý Menu",
              url: "/admin/menus",
              icon: "fas fa-bars",
              children: [],
            });
          }
        }

        this.menuItems = finalItems;
        this.expandActiveParents();
      } catch (e) {
        console.error("❌ Sidebar menu error:", e);
        this.menuItems = [];
      }
    },
    toggle(id) {
      this.openMap[id] = !this.openMap[id];
    },
    isOpen(id) {
      return !!this.openMap[id];
    },
    isActivePath(path) {
      if (!path) return false;
      return (
        this.$route.path === path || this.$route.path.startsWith(path + "/")
      );
    },
    linkClasses(path) {
      const active = this.isActivePath(path);
      return active
        ? "bg-indigo-50 text-indigo-600 font-semibold shadow-sm"
        : "text-gray-600 hover:bg-gray-50 hover:text-gray-900";
    },
    expandActiveParents() {
      this.menuItems.forEach((item) => {
        if (item.children?.some((child) => this.isActivePath(child.url))) {
          this.openMap[item.id] = true;
        }
      });
    },
  },
};
</script>

<style scoped>
.collapse-enter-active,
.collapse-leave-active {
  transition: all 0.3s ease-in-out;
  overflow: hidden;
}
.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
  opacity: 0;
}
.collapse-enter-to,
.collapse-leave-from {
  max-height: 500px;
  opacity: 1;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #e5e7eb;
  border-radius: 20px;
}
</style>
