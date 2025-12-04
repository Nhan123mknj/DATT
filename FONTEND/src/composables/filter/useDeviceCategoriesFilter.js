import { reactive } from "vue";

export function useDeviceCategoriesFilters() {
  const filters = reactive({
    search: "",
    is_active: "",
  });

  const resetFilters = () => {
    filters.search = "";
    filters.is_active = "";
  };

  const buildParams = () => {
    const params = {};
    if (filters.search) params.search = filters.search || undefined;
    if (filters.is_active) params.is_active = filters.is_active || undefined;
    return params;
  };

  return {
    filters,
    resetFilters,
    buildParams,
  };
}