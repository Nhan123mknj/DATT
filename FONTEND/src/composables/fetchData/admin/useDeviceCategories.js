import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { deviceCategoryService } from "../../../services/admin/deviceCategoryService";

export function useDeviceCategories() {
  const toast = useToast();

  const categories = ref([]);
  const pagination = reactive({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    links: [],
  });
  const isLoading = ref(false);

  const loadCategories = async (page = 1, filters = {}) => {
    isLoading.value = true;
    try {
      const params = {
        page,
        search: filters.search || undefined,
      };

      const { data } = await deviceCategoryService.list(params);
      const payload = data.categories;
      // console.log(payload);
      
      categories.value = payload?.data || [];
      pagination.current_page = payload?.current_page || 1;
      pagination.per_page = payload?.per_page || 10;
      pagination.total = payload?.total || 0;
      pagination.last_page = payload?.last_page || 1;
      pagination.links = payload?.links || [];
    } catch (error) {
      if (error.response?.status === 404) {
        categories.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải loại thiết bị");
      }
    } finally {
      isLoading.value = false;
    }
  };

  const deleteCategory = async (categoryId) => {
    if (!confirm("Bạn chắc chắn muốn xóa loại thiết bị này?")) return false;

    try {
      await deviceCategoryService.remove(categoryId);
      toast.success("Đã xóa loại thiết bị");
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa loại thiết bị");
      return false;
    }
  };

  return {
    categories,
    pagination,
    isLoading,
    loadCategories,
    deleteCategory,
  };
}
