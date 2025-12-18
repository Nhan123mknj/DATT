import { storeToRefs } from "pinia";
import { useDeviceCategoryStore } from "../../../stores/deviceCategoryStore";

export function useDeviceCategories() {
  const store = useDeviceCategoryStore();
  const { categories, pagination, isLoading, filters } = storeToRefs(store);
  const { fetchCategories, deleteCategory, addCategory, updateCategory } = store;

  const loadCategories = (page = 1, newFilters = {}) => {
    return fetchCategories(page, newFilters);
  };

  return {
    categories,
    pagination,
    isLoading,
    filters,
    loadCategories,
    deleteCategory,
    addCategory,
    updateCategory,
  };
}
