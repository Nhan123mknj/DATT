import { reactive } from "vue";
export default function () {
    const filters = reactive({
        search: "",
        role: "",
        is_active: "",
    });
    const resetFilters = () => {
        filters.search = "";
        filters.role = "";
        filters.is_active = "";
    };
    return {
        filters,
        resetFilters,
    };
}