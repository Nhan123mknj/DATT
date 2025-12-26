import { storeToRefs } from "pinia";
import { useDeviceUnitStore } from "../../../stores/deviceUnitStore";

export function useDeviceUnits() {
  const store = useDeviceUnitStore();
  const { units, pagination, isLoading } = storeToRefs(store);
  const { filters, fetchUnits, retireUnit, bulkRetireUnits, addUnit, updateUnit } = store;

  const loadDeviceUnits = (page = 1, newFilters = {}) => {
    return fetchUnits(page, newFilters);
  };

  return {
    units,
    pagination,
    isLoading,
    filters,
    loadDeviceUnits,
    retireDeviceUnit: retireUnit,
    bulkRetireDeviceUnits: bulkRetireUnits,
    addUnit,
    updateUnit,
  };
}
