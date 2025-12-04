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

    <!-- Menus Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="menu in menus"
        :key="menu.id"
        class="bg-white rounded-lg shadow border border-gray-200 hover:shadow-lg transition p-5"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ menu.name }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ menu.slug }}</p>
          </div>
          <span
            v-if="menu.is_active"
            class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium"
          >
            Hoạt động
          </span>
          <span
            v-else
            class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium"
          >
            Tắt
          </span>
        </div>

        <p
          v-if="menu.description"
          class="text-sm text-gray-600 mb-3 line-clamp-2"
        >
          {{ menu.description }}
        </p>

        <div
          class="flex items-center justify-between text-sm mb-3 pt-3 border-t border-gray-200"
        >
          <span class="text-gray-600">
            {{ menu.items?.length || 0 }} items
          </span>
        </div>

        <div class="flex gap-2">
          <button
            @click="editMenu(menu)"
            class="flex-1 px-3 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded transition border border-indigo-200"
          >
            <font-awesome-icon icon="edit" class="mr-1" />
            Sửa
          </button>
          <button
            @click="manageItems(menu)"
            class="flex-1 px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded transition border border-blue-200"
          >
            <font-awesome-icon icon="bars" class="mr-1" />
            Items
          </button>
          <button
            @click="deleteMenuConfirm(menu)"
            class="flex-1 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded transition border border-red-200"
          >
            <font-awesome-icon icon="trash" class="mr-1" />
            Xóa
          </button>
        </div>
      </div>

      <div v-if="menus.length === 0" class="col-span-full text-center py-12">
        <font-awesome-icon
          icon="inbox"
          class="text-4xl text-gray-300 mb-3 block"
        />
        <p class="text-gray-500 font-medium">Chưa có menu nào</p>
      </div>
    </div>

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
import { useToast } from "vue-toastification";
import ModalForm from "../../components/ModalForm.vue";
import MenuItem from "./MenuItemRow.vue";
import { menuService } from "../../services/admin/menuService";

export default {
  name: "MenuManagement",
  components: {
    ModalForm,
    MenuItem,
  },
  data() {
    return {
      menus: [],
      currentMenu: null,
      currentMenuItems: [],
      editingMenu: null,
      editingItem: null,
      loading: false,
      saving: false,
      showMenuModal: false,
      showItemsModal: false,
      showItemModal: false,
      menuForm: {
        name: "",
        slug: "",
        description: "",
        is_active: true,
        sort_order: 0,
      },
      itemForm: {
        label: "",
        url: "",
        icon: "",
        badge: "",
        badge_color: "primary",
        sort_order: 0,
        is_active: true,
        parent_id: null,
        description: "",
      },
      errors: {},
    };
  },
  computed: {
    rootItems() {
      return this.currentMenuItems.filter((item) => !item.parent_id);
    },
  },
  mounted() {
    this.loadMenus();
  },
  methods: {
    async loadMenus() {
      this.loading = true;
      try {
        const { data } = await menuService.list();
        this.menus = data.data || [];
      } catch (error) {
        this.toast.error("Lỗi tải menu");
      } finally {
        this.loading = false;
      }
    },
    openCreateMenu() {
      this.editingMenu = null;
      Object.assign(this.menuForm, {
        name: "",
        slug: "",
        description: "",
        is_active: true,
        sort_order: 0,
      });
      this.errors = {};
      this.showMenuModal = true;
    },
    editMenu(menu) {
      this.editingMenu = menu;
      Object.assign(this.menuForm, menu);
      this.errors = {};
      this.showMenuModal = true;
    },
    closeMenuModal() {
      this.showMenuModal = false;
      this.editingMenu = null;
    },
    async saveMenu() {
      this.saving = true;
      this.errors = {};

      try {
        if (this.editingMenu) {
          await menuService.update(this.editingMenu.id, this.menuForm);
          this.toast.success("Cập nhật menu thành công");
        } else {
          await menuService.create(this.menuForm);
          this.toast.success("Tạo menu thành công");
        }
        this.loadMenus();
        this.closeMenuModal();
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        } else {
          this.toast.error("Lỗi lưu menu");
        }
      } finally {
        this.saving = false;
      }
    },
    async deleteMenuConfirm(menu) {
      if (!confirm(`Xóa menu "${menu.name}"?`)) return;
      try {
        await menuService.delete(menu.id);
        this.toast.success("Xóa menu thành công");
        this.loadMenus();
      } catch {
        this.toast.error("Lỗi xóa menu");
      }
    },
    async manageItems(menu) {
      this.currentMenu = menu;
      try {
        const { data } = await menuService.get(menu.id);
        this.currentMenuItems = data.data.items || [];
      } catch {
        this.toast.error("Lỗi tải items");
      }
      this.showItemsModal = true;
    },
    closeItemsModal() {
      this.showItemsModal = false;
      this.currentMenu = null;
      this.currentMenuItems = [];
    },
    openCreateItem() {
      this.editingItem = null;
      Object.assign(this.itemForm, {
        menu_id: this.currentMenu.id,
        label: "",
        url: "",
        icon: "",
        badge: "",
        badge_color: "primary",
        sort_order: 0,
        is_active: true,
        parent_id: null,
        description: "",
      });
      this.errors = {};
      this.showItemModal = true;
    },
    editItem(item) {
      this.editingItem = item;
      Object.assign(this.itemForm, {
        ...item,
        menu_id: this.currentMenu.id,
      });
      this.errors = {};
      this.showItemModal = true;
    },
    closeItemModal() {
      this.showItemModal = false;
      this.editingItem = null;
    },
    async saveItem() {
      this.saving = true;
      this.errors = {};

      try {
        if (this.editingItem) {
          await menuService.updateItem(this.editingItem.id, this.itemForm);
          this.toast.success("Cập nhật item thành công");
        } else {
          await menuService.createItem(this.itemForm);
          this.toast.success("Tạo item thành công");
        }
        this.manageItems(this.currentMenu);
        this.closeItemModal();
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        } else {
          this.toast.error("Lỗi lưu item");
        }
      } finally {
        this.saving = false;
      }
    },
    async deleteItemConfirm(item) {
      if (!confirm(`Xóa item "${item.label}"?`)) return;
      try {
        await menuService.deleteItem(item.id);
        this.toast.success("Xóa item thành công");
        this.manageItems(this.currentMenu);
      } catch {
        this.toast.error("Lỗi xóa item");
      }
    },
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
};
</script>
