<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Quản lý Menu</h1>
        <p class="text-sm text-gray-500 mt-1">
          Tạo và quản lý menu cho website
        </p>
      </div>
      <button
        @click="openCreateMenu"
        class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium"
      >
        <font-awesome-icon icon="plus" class="mr-2" />
        Tạo Menu Mới
      </button>
    </div>

    <!-- Menus Table -->
    <Table
      :data="menus"
      :headers="tableHeaders"
      @edit="editMenu"
      @delete="deleteMenuConfirm"
    >
      <template #name="{ item }">
        <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
      </template>

      <template #slug="{ item }">
        <span
          class="px-2 py-1 text-xs font-mono bg-gray-100 text-gray-600 rounded border border-gray-200"
        >
          {{ item.slug }}
        </span>
      </template>

      <template #description="{ item }">
        <div class="text-sm text-gray-500 line-clamp-1 max-w-xs">
          {{ item.description || "—" }}
        </div>
      </template>

      <template #status="{ item }">
        <span
          v-if="item.is_active"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
        >
          Hoạt động
        </span>
        <span
          v-else
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
        >
          Tắt
        </span>
      </template>

      <template #items="{ item }">
        <span class="text-sm text-gray-500">{{ item.items?.length || 0 }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex justify-end gap-2">
          <button
            @click="manageItems(item)"
            class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition-colors text-sm font-medium"
          >
            Items
          </button>
          <button
            @click="editMenu(item)"
            class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors text-sm font-medium"
          >
            Sửa
          </button>
          <button
            @click="deleteMenuConfirm(item)"
            class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors text-sm font-medium"
          >
            Xóa
          </button>
        </div>
      </template>

      <template #empty>
        <div class="flex flex-col items-center justify-center py-8">
          <font-awesome-icon icon="inbox" class="text-4xl text-gray-300 mb-3" />
          <p class="text-gray-500">Chưa có menu nào</p>
        </div>
      </template>
    </Table>

    <!-- Menu Form Modal -->
    <ModalForm
      :show="showMenuModal"
      :title="editingMenu ? 'Chỉnh sửa Menu' : 'Tạo Menu Mới'"
      @close="closeMenuModal"
      @submit="saveMenu"
      size="medium"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Tên Menu <span class="text-red-500">*</span>
          </label>
          <input
            v-model="menuForm.name"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
            :disabled="!!editingMenu"
          />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">
            {{ errors.name?.[0] }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Slug <span class="text-red-500">*</span>
          </label>
          <input
            v-model="menuForm.slug"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
            placeholder="main, header, footer"
            :disabled="!!editingMenu"
          />
          <p v-if="errors.slug" class="text-xs text-red-500 mt-1">
            {{ errors.slug?.[0] }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Mô tả
          </label>
          <textarea
            v-model="menuForm.description"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 resize-none"
            rows="3"
            placeholder="Mô tả menu..."
          ></textarea>
        </div>

        <div class="flex items-center">
          <input
            v-model="menuForm.is_active"
            type="checkbox"
            class="w-4 h-4 rounded border-gray-300 text-indigo-600"
          />
          <label class="ml-2 text-sm text-gray-700">Kích hoạt menu</label>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3">
          <button
            type="button"
            @click="closeMenuModal"
            class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? "Đang lưu..." : "Lưu" }}
          </button>
        </div>
      </template>
    </ModalForm>

    <!-- Menu Items Modal -->
    <ModalForm
      :show="showItemsModal"
      :title="`Items: ${currentMenu?.name}`"
      @close="closeItemsModal"
      size="large"
    >
      <div class="space-y-4">
        <button
          @click="openCreateItem"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium"
        >
          <font-awesome-icon icon="plus" class="mr-2" />
          Thêm Item
        </button>

        <!-- Items Tree -->
        <div class="space-y-2 max-h-96 overflow-y-auto">
          <MenuItem
            v-for="item in currentMenuItems"
            :key="item.id"
            :item="item"
            :depth="0"
            @edit="editItem"
            @delete="deleteItemConfirm"
          />

          <div
            v-if="currentMenuItems.length === 0"
            class="text-center py-6 text-gray-500"
          >
            <p>Chưa có item nào</p>
          </div>
        </div>
      </div>

      <template #footer>
        <button
          @click="closeItemsModal"
          class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 font-medium"
        >
          Đóng
        </button>
      </template>
    </ModalForm>

    <!-- Item Form Modal -->
    <ModalForm
      :show="showItemModal"
      :title="editingItem ? 'Sửa Item' : 'Thêm Item Mới'"
      @close="closeItemModal"
      @submit="saveItem"
      size="medium"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Tên Item <span class="text-red-500">*</span>
          </label>
          <input
            v-model="itemForm.label"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
            placeholder="Dashboard, Quản lý..."
          />
          <p v-if="errors.label" class="text-xs text-red-500 mt-1">
            {{ errors.label?.[0] }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            URL
          </label>
          <input
            v-model="itemForm.url"
            type="text"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
            placeholder="/dashboard, /users"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Icon
            </label>
            <input
              v-model="itemForm.icon"
              type="text"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
              placeholder="chart-line, users..."
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Thứ tự
            </label>
            <input
              v-model.number="itemForm.sort_order"
              type="number"
              min="0"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Parent Item
          </label>
          <select
            v-model.number="itemForm.parent_id"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          >
            <option :value="null">Không (Item chính)</option>
            <option v-for="item in rootItems" :key="item.id" :value="item.id">
              {{ item.label }}
            </option>
          </select>
        </div>

        <div class="flex items-center">
          <input
            v-model="itemForm.is_active"
            type="checkbox"
            class="w-4 h-4 rounded border-gray-300 text-indigo-600"
          />
          <label class="ml-2 text-sm text-gray-700">Kích hoạt item</label>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3">
          <button
            type="button"
            @click="closeItemModal"
            class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? "Đang lưu..." : "Lưu" }}
          </button>
        </div>
      </template>
    </ModalForm>
  </div>
</template>

<script>
import { onMounted } from "vue";
import Table from "../../components/common/Table.vue";
import ModalForm from "../../components/ModalForm.vue";
import MenuItem from "./MenuItemRow.vue";
import { useMenu } from "../../composables/fetchData/admin/useMenu";

export default {
  name: "MenuManagement",
  components: {
    Table,
    ModalForm,
    MenuItem,
  },
  setup() {
    const {
      menus,
      currentMenu,
      currentMenuItems,
      editingMenu,
      editingItem,
      loading,
      saving,
      showMenuModal,
      showItemsModal,
      showItemModal,
      menuForm,
      itemForm,
      errors,
      rootItems,
      loadMenus,
      openCreateMenu,
      editMenu,
      closeMenuModal,
      saveMenu,
      deleteMenuConfirm,
      manageItems,
      closeItemsModal,
      openCreateItem,
      editItem,
      closeItemModal,
      saveItem,
      deleteItemConfirm,
    } = useMenu();

    const tableHeaders = {
      name: "Tên Menu",
      slug: "Slug",
      description: "Mô tả",
      status: "Trạng thái",
      items: "Items",
    };

    onMounted(() => {
      loadMenus();
    });

    return {
      menus,
      tableHeaders,
      currentMenu,
      currentMenuItems,
      editingMenu,
      editingItem,
      loading,
      saving,
      showMenuModal,
      showItemsModal,
      showItemModal,
      menuForm,
      itemForm,
      errors,
      rootItems,
      openCreateMenu,
      editMenu,
      closeMenuModal,
      saveMenu,
      deleteMenuConfirm,
      manageItems,
      closeItemsModal,
      openCreateItem,
      editItem,
      closeItemModal,
      saveItem,
      deleteItemConfirm,
    };
  },
};
</script>
