import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { usersService } from "../../../services/admin/usersService";

export function useUsers() {
  const toast = useToast();

  const users = ref([]);
  const pagination = reactive({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    links: [],
  });
  const isLoading = ref(false);

  const loadUsers = async (page = 1, filters = {}) => {
    isLoading.value = true;
    try {
      const params = {
        page,
        search: filters.search || undefined,
        role: filters.role || undefined,
        is_active: filters.is_active || undefined,
      };

      const { data } = await usersService.getAllUser(params);
      users.value = data.data || [];
      pagination.current_page = data.current_page || 1;
      pagination.per_page = data.per_page || 10;
      pagination.total = data.total || 0;
      pagination.last_page = data.last_page || 1;
      pagination.links = data.links || [];
    } catch (error) {
      if (error.response?.status === 404) {
        users.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải người dùng");
      }
    } finally {
      isLoading.value = false;
    }
  };

  const deleteUser = async (userId) => {
    if (!confirm("Bạn chắc chắn muốn xóa người dùng này?")) return false;

    try {
      await usersService.deleteUser(userId);
      toast.success("Đã xóa người dùng");
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa người dùng");
      return false;
    }
  };

  return {
    users,
    pagination,
    isLoading,
    loadUsers,
    deleteUser,
  };
}
