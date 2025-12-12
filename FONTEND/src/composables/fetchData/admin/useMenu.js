import { ref, reactive, computed } from "vue";
import { useToast } from "vue-toastification";
import { menuService } from "../../../services/admin/menuService";

export function useMenu() {
  const toast = useToast();

  // State
  const menus = ref([]);
  const currentMenu = ref(null);
  const currentMenuItems = ref([]);
  const editingMenu = ref(null);
  const editingItem = ref(null);
  const loading = ref(false);
  const saving = ref(false);
  const showMenuModal = ref(false);
  const showItemsModal = ref(false);
  const showItemModal = ref(false);
  const errors = ref({});

  const menuForm = reactive({
    id: null,
    name: "",
    slug: "",
    description: "",
    is_active: true,
    sort_order: 0,
  });

  const itemForm = reactive({
    id: null,
    menu_id: null,
    parent_id: null,
    label: "",
    url: "",
    icon: "",
    badge: "",
    badge_color: "primary",
    sort_order: 0,
    is_active: true,
    description: "",
  });

  // Computed
  const rootItems = computed(() => {
    return currentMenuItems.value.filter((item) => !item.parent_id);
  });

  // Methods
  const loadMenus = async () => {
    loading.value = true;
    try {
      const { data } = await menuService.list();
    //   console.log(data);
      menus.value = data.data || [];
    } catch (error) {
      toast.error("Lỗi tải menu");
    } finally {
      loading.value = false;
    }
  };

  const openCreateMenu = () => {
    editingMenu.value = null;
    Object.assign(menuForm, {
      id: null,
      name: "",
      slug: "",
      description: "",
      is_active: true,
      sort_order: 0,
    });
    errors.value = {};
    showMenuModal.value = true;
  };

  const editMenu = (menu) => {
    editingMenu.value = menu;
    Object.assign(menuForm, {
      id: menu.id,
      name: menu.name,
      slug: menu.slug,
      description: menu.description,
      is_active: menu.is_active,
      sort_order: menu.sort_order,
    });
    errors.value = {};
    showMenuModal.value = true;
  };

  const closeMenuModal = () => {
    showMenuModal.value = false;
    editingMenu.value = null;
  };

  const saveMenu = async () => {
    saving.value = true;
    errors.value = {};

    try {
      if (editingMenu.value) {
        await menuService.update(editingMenu.value.id, menuForm);
        toast.success("Cập nhật menu thành công");
      } else {
        await menuService.create(menuForm);
        toast.success("Tạo menu thành công");
      }
      loadMenus();
      closeMenuModal();
    } catch (error) {
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
      } else {
        toast.error("Lỗi lưu menu");
      }
    } finally {
      saving.value = false;
    }
  };

  const deleteMenuConfirm = async (menu) => {
    if (!confirm(`Xóa menu "${menu.name}"?`)) return;
    try {
      await menuService.delete(menu.id);
      toast.success("Xóa menu thành công");
      loadMenus();
    } catch {
      toast.error("Lỗi xóa menu");
    }
  };

  const manageItems = async (menu) => {
    currentMenu.value = menu;
    try {
      const { data } = await menuService.get(menu.id);
      currentMenuItems.value = data.data.items || [];
    } catch {
      toast.error("Lỗi tải items");
    }
    showItemsModal.value = true;
  };

  const closeItemsModal = () => {
    showItemsModal.value = false;
    currentMenu.value = null;
    currentMenuItems.value = [];
  };

  const openCreateItem = () => {
    editingItem.value = null;
    Object.assign(itemForm, {
      id: null,
      menu_id: currentMenu.value.id,
      parent_id: null,
      label: "",
      url: "",
      icon: "",
      badge: "",
      badge_color: "primary",
      sort_order: 0,
      is_active: true,
      description: "",
    });
    errors.value = {};
    showItemModal.value = true;
  };

  const editItem = (item) => {
    editingItem.value = item;
    Object.assign(itemForm, {
      id: item.id,
      menu_id: currentMenu.value.id,
      parent_id: item.parent_id,
      label: item.label,
      url: item.url,
      icon: item.icon,
      badge: item.badge,
      badge_color: item.badge_color,
      sort_order: item.sort_order,
      is_active: item.is_active,
      description: item.description,
    });
    errors.value = {};
    showItemModal.value = true;
  };

  const closeItemModal = () => {
    showItemModal.value = false;
    editingItem.value = null;
  };

  const saveItem = async () => {
    saving.value = true;
    errors.value = {};

    try {
      const data = {
        ...itemForm,
        menu_id: currentMenu.value.id,
      };

      if (itemForm.id) {
        await menuService.updateItem(itemForm.id, data);
        toast.success("Cập nhật item thành công");
      } else {
        await menuService.createItem(data);
        toast.success("Thêm item thành công");
      }
      manageItems(currentMenu.value);
      closeItemModal();
    } catch (error) {
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
      } else {
        toast.error("Lỗi lưu item");
      }
    } finally {
      saving.value = false;
    }
  };

  const deleteItemConfirm = async (item) => {
    if (!confirm(`Xóa item "${item.label}"?`)) return;
    try {
      await menuService.deleteItem(item.id);
      toast.success("Xóa item thành công");
      manageItems(currentMenu.value);
    } catch {
      toast.error("Lỗi xóa item");
    }
  };

  return {
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
  };
}
