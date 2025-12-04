import { reactive } from "vue";

export function useDeviceFilters() {
  const filters = reactive({
    search: "",
    category_id: "",
    is_active: "",
  });

  const resetFilters = () => {
    filters.search = "";
    filters.category_id = "";
    filters.is_active = "";
  };

  const buildParams = () => {
    const params = {};
    if (filters.search) params.search = filters.search || undefined;
    if (filters.category_id) params.category_id = filters.category_id || undefined;
    if (filters.is_active) params.is_active = filters.is_active || undefined;
    return params;
  };

  return {
    filters,
    resetFilters,
    buildParams,
  };
}