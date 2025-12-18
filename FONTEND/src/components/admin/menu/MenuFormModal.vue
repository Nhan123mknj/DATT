<template>
  <ModalForm
    :show="menuStore.showMenuModal"
    :title="
      menuStore.editingMenu ? 'Chỉnh sửa Menu Item' : 'Thêm Menu Item Mới'
    "
    @close="menuStore.closeMenuModal()"
    @submit="menuStore.saveMenu()"
    size="medium"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Tên <span class="text-red-500">*</span>
        </label>
        <input
          v-model="menuStore.menuForm.label"
          type="text"
          placeholder="Dashboard, Quản lý..."
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
        />
        <p v-if="menuStore.errors.label" class="text-xs text-red-500 mt-1">
          {{ menuStore.errors.label[0] }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
        <input
          v-model="menuStore.menuForm.url"
          type="text"
          placeholder="/dashboard, /users"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
        />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Icon</label
          >
          <input
            v-model="menuStore.menuForm.icon"
            type="text"
            placeholder="home, user, settings..."
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Thứ tự</label
          >
          <input
            v-model.number="menuStore.menuForm.sort_order"
            type="number"
            min="0"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Badge</label
        >
        <input
          v-model="menuStore.menuForm.badge"
          type="text"
          placeholder="New, Beta..."
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Badge Color</label
        >
        <select
          v-model="menuStore.menuForm.badge_color"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
        >
          <option value="blue">Blue</option>
          <option value="red">Red</option>
          <option value="green">Green</option>
          <option value="yellow">Yellow</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Parent Item</label
        >
        <select
          v-model.number="menuStore.menuForm.parent_id"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
        >
          <option :value="null">Không (Root Item)</option>
          <option v-for="item in rootItems" :key="item.id" :value="item.id">
            {{ item.label }}
          </option>
        </select>
      </div>

      <div class="flex items-center">
        <input
          v-model="menuStore.menuForm.is_active"
          type="checkbox"
          class="w-4 h-4 rounded border-gray-300 text-indigo-600"
        />
        <label class="ml-2 text-sm text-gray-700">Kích hoạt</label>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3">
        <button
          type="button"
          @click="menuStore.closeMenuModal()"
          class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium"
        >
          Hủy
        </button>
        <button
          type="submit"
          :disabled="menuStore.saving"
          class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50"
        >
          {{ menuStore.saving ? "Đang lưu..." : "Lưu" }}
        </button>
      </div>
    </template>
  </ModalForm>
</template>

<script setup>
import { computed } from "vue";
import { useMenuStore } from "../../../stores/menuStore";
import ModalForm from "../../ModalForm.vue";

const menuStore = useMenuStore();

const rootItems = computed(() => {
  return menuStore.menus.filter((item) => !item.parent_id);
});
</script>
