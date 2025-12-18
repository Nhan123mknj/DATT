import { storeToRefs } from "pinia";
import { useDeviceStore } from "../../../stores/deviceStore";

export function useDevices() {
  const store = useDeviceStore();
  const { devices, pagination, isLoading, filters } = storeToRefs(store);
  const { fetchDevices, deleteDevice } = store;

  const loadDevices = (page = 1, newFilters = {}) => {
    return fetchDevices(page, newFilters);
  };

  return {
    devices,
    pagination,
    isLoading,
    filters,
    loadDevices,
    deleteDevice,
  };
}
