<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Quản lý Menu</h1>
        <p class="text-sm text-gray-500 mt-1">
          Tạo và quản lý menu items cho website
        </p>
      </div>
      <button
        @click="menuStore.openCreateMenu()"
        class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium"
      >
        <font-awesome-icon icon="plus" class="mr-2" />
        Thêm Menu Item
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
      <div v-if="menuStore.loading" class="text-center py-8">
        <div class="inline-flex items-center gap-2 text-indigo-600">
          <font-awesome-icon icon="spinner" class="animate-spin" />
          <span>Đang tải...</span>
        </div>
      </div>

      <div v-else>
        <Table :data="menuStore.menus" :headers="tableHeaders">
          <template #label="{ item }">
            <div class="flex items-center gap-2">
              <font-awesome-icon
                v-if="item.icon"
                :icon="item.icon"
                class="text-gray-400"
              />
              <span class="font-medium">{{ item.label }}</span>
              <span
                v-if="item.badge"
                class="px-2 py-0.5 rounded text-xs font-medium"
                :class="`bg-${item.badge_color}-100 text-${item.badge_color}-700`"
              >
                {{ item.badge }}
              </span>
            </div>
          </template>

          <template #url="{ item }">
            <span class="text-sm font-mono text-gray-600">{{
              item.url || "—"
            }}</span>
          </template>

          <template #parent="{ item }">
            <span class="text-sm text-gray-600">
              {{ item.parent_id ? getParentLabel(item.parent_id) : "Root" }}
            </span>
          </template>

          <template #is_active="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="
                item.is_active
                  ? 'bg-green-100 text-green-700'
                  : 'bg-gray-100 text-gray-600'
              "
            >
              {{ item.is_active ? "Hoạt động" : "Tắt" }}
            </span>
          </template>

          <template #sort_order="{ item }">
            <span class="text-sm text-gray-600">{{ item.sort_order }}</span>
          </template>

          <template #actions="{ item }">
            <div class="flex gap-2">
              <button
                @click="menuStore.editMenu(item)"
                class="px-3 py-1 rounded-lg border border-gray-200 hover:bg-gray-50 text-sm"
              >
                Sửa
              </button>
              <button
                @click="menuStore.deleteMenuConfirm(item)"
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm"
              >
                Xóa
              </button>
            </div>
          </template>
        </Table>

        <div v-if="menuStore.menus.length === 0" class="text-center py-12">
          <font-awesome-icon icon="inbox" class="text-4xl text-gray-300 mb-3" />
          <p class="text-gray-500">Chưa có menu item nào</p>
        </div>
      </div>
    </div>

    <MenuFormModal />
  </div>
</template>

<script setup>
import { onMounted, computed } from "vue";
import { useMenuStore } from "../../stores/menuStore";
import Table from "../../components/common/Table.vue";
import MenuFormModal from "../../components/admin/menu/MenuFormModal.vue";

const menuStore = useMenuStore();

const tableHeaders = {
  label: "Tên",
  url: "URL",
  parent: "Cha",
  is_active: "Trạng thái",
  sort_order: "Thứ tự",
};

const getParentLabel = (parentId) => {
  const parent = menuStore.menus.find((m) => m.id === parentId);
  return parent ? parent.label : "—";
};

onMounted(() => {
  menuStore.loadMenus();
});
</script>
