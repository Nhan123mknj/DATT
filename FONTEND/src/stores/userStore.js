import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { usersService } from "../services/admin/usersService";

export const useUserStore = defineStore("user", () => {
  const toast = useToast();

  const users = ref([]);
  const loading = ref(false);
  const saving = ref(false);
  const errors = ref({});

  const pagination = reactive({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    links: [],
  });

  const filters = reactive({
    search: "",
    role: "",
    is_active: undefined,
  });

  const showModal = ref(false);
  const modalMode = ref("create");

  const userForm = ref({
    id: null,
    name: "",
    email: "",
    role: "",
    is_active: 1,
  });

  const editingUser = ref(null);

  const loadUsers = async (page = 1, customFilters = {}) => {
    loading.value = true;
    try {
      if (Object.keys(customFilters).length > 0) {
        Object.assign(filters, customFilters);
      }

      const params = {
        page,
        search: filters.search || undefined,
        role: filters.role || undefined,
        is_active: filters.is_active,
      };

      const { data } = await usersService.getAllUser(params);
      
      users.value = data.data || [];
      pagination.current_page = data.current_page || 1;
      pagination.per_page = data.per_page || 10;
      pagination.total = data.total || 0;
      pagination.last_page = data.last_page || 1;
      pagination.links = data.links || [];
    } catch (error) {
    //   console.error(error);
      users.value = [];
    } finally {
      loading.value = false;
    }
  };

  const openCreate = () => {
    editingUser.value = null;
    modalMode.value = "create";
    userForm.value = {
      id: null,
      name: "",
      email: "",
      role: "",
      credit_score: 0,
      is_active: 1,
    };
    errors.value = {};
    showModal.value = true;
  };

  const openEdit = (user) => {
    editingUser.value = user;
    modalMode.value = "edit";
    userForm.value = {
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role,
      credit_score: user.credit_score,
      is_active: user.is_active,
    };
    errors.value = {};
    showModal.value = true;
  };

  const closeModal = () => {
    showModal.value = false;
    editingUser.value = null;
    modalMode.value = "create";
    errors.value = {};
  };

  const deleteUser = async (userId) => {
    if (!confirm("Bạn chắc chắn muốn xóa người dùng này?")) return false;

    try {
      await usersService.deleteUser(userId);
      toast.success("Đã xóa người dùng");
      users.value = users.value.filter((u) => u.id !== userId);
      pagination.total--;
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa người dùng");
      return false;
    }
  };

  const resetFilters = () => {
    filters.search = "";
    filters.role = "";
    filters.is_active = undefined;
  };

  return {
    users,
    loading,
    saving,
    errors,
    pagination,
    filters,
    showModal,
    modalMode,
    userForm,
    editingUser,
    loadUsers,
    openCreate,
    openEdit,
    closeModal,
    deleteUser,
    resetFilters,
  };
});
