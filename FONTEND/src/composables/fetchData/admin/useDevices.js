import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { devicesService } from "../../../services/devices/devicesService";

export function useDevices() {
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

  const loadDevices = async (page = 1, filters = {}) => {
    isLoading.value = true;
    try {
      const params = {
        page,
        search: filters.search || undefined,
        category_id: filters.category_id || undefined,
        is_active: filters.is_active,
      };

      const { data } = await devicesService.list(params);
      const payload = data.devices;

      devices.value = payload?.data || [];
      pagination.current_page = payload?.current_page || 1;
      pagination.per_page = payload?.per_page || 10;
      pagination.total = payload?.total || 0;
      pagination.last_page = payload?.last_page || 1;
      pagination.links = payload?.links || [];
    } catch (error) {
      if (error.response?.status === 404) {
        devices.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải thiết bị");
      }
    } finally {
      isLoading.value = false;
    }
  };

  const deleteDevice = async (deviceId) => {
    if (!confirm("Bạn chắc chắn muốn xóa thiết bị này?")) return false;

    try {
      await devicesService.remove(deviceId);
      toast.success("Đã xóa thiết bị");
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa thiết bị");
      return false;
    }
  };

  return {
    devices,
    pagination,
    isLoading,
    loadDevices,
    deleteDevice,
  };
}
