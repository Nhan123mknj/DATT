import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { deviceCategoriesService } from "../services/devices/deviceCategoriesService";

export const useDeviceCategoryStore = defineStore("deviceCategory", () => {
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
  const filters = reactive({
    search: "",
  });

  const fetchCategories = async (page = 1, customFilters = {}) => {
    isLoading.value = true;
    try {
      if (Object.keys(customFilters).length > 0) {
        Object.assign(filters, customFilters);
      }

      const params = {
        page,
        search: filters.search || undefined,
      };

      const { data } = await deviceCategoriesService.list(params);
      const payload = data.categories;

      if (Array.isArray(payload)) {
         categories.value = payload;
         pagination.current_page = data.current_page || 1;
         pagination.per_page = data.per_page || 10;
         pagination.total = data.total || 0;
         pagination.last_page = data.last_page || 1;
         pagination.links = data.links || [];
      } else if (payload?.data && Array.isArray(payload.data)) {
         categories.value = payload.data;
         pagination.current_page = payload.current_page || 1;
         pagination.per_page = payload.per_page || 10;
         pagination.total = payload.total || 0;
         pagination.last_page = payload.last_page || 1;
         pagination.links = payload.links || [];
      } else {
         categories.value = [];
      }

    } catch (error) {
      if (error.response?.status === 404) {
        categories.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải danh mục thiết bị");
        console.error(error);
      }
    } finally {
      isLoading.value = false;
    }
  };

  const addCategory = async (categoryData) => {
    try {
      await deviceCategoriesService.create(categoryData);
      toast.success("Thêm danh mục thành công");
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể thêm danh mục");
      return false;
    }
  };

  const updateCategory = async (id, categoryData) => {
    try {
      await deviceCategoriesService.update(id, categoryData);
      toast.success("Cập nhật danh mục thành công");
      // Update local state
      const index = categories.value.findIndex(c => c.id === id);
      if (index !== -1) {
          categories.value[index] = { ...categories.value[index], ...categoryData };
      }
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể cập nhật danh mục");
      return false;
    }
  };

  const deleteCategory = async (categoryId) => {
    if (!confirm("Bạn chắc chắn muốn xóa danh mục này?")) return false;

    try {
      await deviceCategoriesService.remove(categoryId);
      toast.success("Đã xóa danh mục");
      categories.value = categories.value.filter((c) => c.id !== categoryId);
      pagination.total--;
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa danh mục");
      return false;
    }
  };

  return {
    categories,
    pagination,
    isLoading,
    filters,
    fetchCategories,
    addCategory,
    updateCategory,
    deleteCategory,
  };
});
