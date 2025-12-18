import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { deviceService } from "../services/admin/deviceService";

export const useDeviceStore = defineStore("device", () => {
  const toast = useToast();

  const devices = ref([]);
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
    category_id: "",
    is_active: undefined,
  });

  const fetchDevices = async (page = 1, customFilters = {}) => {
    isLoading.value = true;
    try {
      if (Object.keys(customFilters).length > 0) {
        Object.assign(filters, customFilters);
      }

      const params = {
        page,
        search: filters.search || undefined,
        category_id: filters.category_id || undefined,
        is_active: filters.is_active,
      };

      const { data } = await deviceService.list(params);
      const payload = data.devices;

      if (Array.isArray(payload)) {
         devices.value = payload;
         pagination.current_page = data.current_page || 1;
         pagination.per_page = data.per_page || 10;
         pagination.total = data.total || 0;
         pagination.last_page = data.last_page || 1;
         pagination.links = data.links || [];
      } else if (payload?.data && Array.isArray(payload.data)) {
         devices.value = payload.data;
         pagination.current_page = payload.current_page || 1;
         pagination.per_page = payload.per_page || 10;
         pagination.total = payload.total || 0;
         pagination.last_page = payload.last_page || 1;
         pagination.links = payload.links || [];
      } else {
         devices.value = [];
      }

    } catch (error) {
      if (error.response?.status === 404) {
        devices.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải thiết bị");
        console.error(error);
      }
    } finally {
      isLoading.value = false;
    }
  };

  const deleteDevice = async (deviceId) => {
    if (!confirm("Bạn chắc chắn muốn xóa thiết bị này?")) return false;

    try {
      await deviceService.remove(deviceId);
      toast.success("Đã xóa thiết bị");
      devices.value = devices.value.filter((d) => d.id !== deviceId);
      pagination.total--;
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa thiết bị");
      return false;
    }
  };

  // Action to update total_units for a specific device (called when unit is added/removed)
  const updateDeviceUnitCount = (deviceId, change) => {
    const device = devices.value.find(d => d.id === deviceId);
    if (device) {
      device.total_units = (device.total_units || 0) + change;
    }
  };

  return {
    devices,
    pagination,
    isLoading,
    filters,
    fetchDevices,
    deleteDevice,
    updateDeviceUnitCount
  };
});
