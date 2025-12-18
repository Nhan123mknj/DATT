import { defineStore } from "pinia";
import { ref } from "vue";
import { useToast } from "vue-toastification";
import { menuService } from "../services/admin/menuService";

export const useMenuStore = defineStore("menu", () => {
  const toast = useToast();

  const menus = ref([]);
  const loading = ref(false);
  const saving = ref(false);
  const errors = ref({});

  const showMenuModal = ref(false);

  const menuForm = ref({
    parent_id: null,
    label: "",
    url: "",
    icon: "",
    badge: "",
    badge_color: "blue",
    sort_order: 0,
    is_active: true,
  });

  const editingMenu = ref(null);

  const loadMenus = async () => {
    loading.value = true;
    errors.value = {};
    try {
      const { data } = await menuService.list();
      menus.value = data.data || [];
    } catch (error) {
      toast.error("Không thể tải danh sách menu");
      console.error(error);
      menus.value = [];
    } finally {
      loading.value = false;
    }
  };

  const openCreateMenu = () => {
    editingMenu.value = null;
    menuForm.value = {
      parent_id: null,
      label: "",
      url: "",
      icon: "",
      badge: "",
      badge_color: "blue",
      sort_order: 0,
      is_active: true,
    };
    errors.value = {};
    showMenuModal.value = true;
  };

  const editMenu = (menu) => {
    editingMenu.value = menu;
    menuForm.value = {
      parent_id: menu.parent_id,
      label: menu.label,
      url: menu.url || "",
      icon: menu.icon || "",
      badge: menu.badge || "",
      badge_color: menu.badge_color || "blue",
      sort_order: menu.sort_order || 0,
      is_active: menu.is_active,
    };
    errors.value = {};
    showMenuModal.value = true;
  };

  const saveMenu = async () => {
    saving.value = true;
    errors.value = {};
    try {
      if (editingMenu.value) {
        const { data } = await menuService.update(
          editingMenu.value.id,
          menuForm.value
        );
        const index = menus.value.findIndex((m) => m.id === editingMenu.value.id);
        if (index !== -1) {
          menus.value[index] = data.data;
        }
        toast.success("Cập nhật menu item thành công");
      } else {
        const { data } = await menuService.create(menuForm.value);
        menus.value.push(data.data);
        toast.success("Tạo menu item thành công");
      }
      closeMenuModal();
      await loadMenus();
    } catch (error) {
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
      } else {
        toast.error(
          error.response?.data?.message || "Không thể lưu menu"
        );
      }
    } finally {
      saving.value = false;
    }
  };

  const deleteMenuConfirm = async (menu) => {
    if (!confirm(`Bạn chắc chắn muốn xóa menu item "${menu.label}"?`)) return;

    try {
      await menuService.delete(menu.id);
      menus.value = menus.value.filter((m) => m.id !== menu.id);
      toast.success("Xóa menu item thành công");
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa menu");
    }
  };

  const closeMenuModal = () => {
    showMenuModal.value = false;
    editingMenu.value = null;
    errors.value = {};
  };

  return {
    menus,
    loading,
    saving,
    errors,
    showMenuModal,
    menuForm,
    editingMenu,
    loadMenus,
    openCreateMenu,
    editMenu,
    saveMenu,
    deleteMenuConfirm,
    closeMenuModal,
  };
});
