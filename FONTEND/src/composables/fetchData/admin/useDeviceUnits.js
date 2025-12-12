import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { deviceUnitService } from "../../../services/admin/deviceUnitService";

export function useDeviceUnits() {
  const toast = useToast();

  const deviceUnits = ref([]);
  const pagination = reactive({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    links: [],
  });
  const isLoading = ref(false);

  const loadDeviceUnits = async (page = 1, filters = {}) => {
    isLoading.value = true;
    try {
      const params = {
        page,
        search: filters.search || undefined,
        device_id: filters.device_id || undefined,
        status: filters.status || undefined,
      };

      const { data } = await deviceUnitService.list(params);
      
      // console.log(data);
      
      const payload = data.data;

      deviceUnits.value = payload || [];
      pagination.current_page = payload?.current_page || 1;
      pagination.per_page = payload?.per_page || 10;
      pagination.total = payload?.total || 0;
      pagination.last_page = payload?.last_page || 1;
      pagination.links = payload?.links || [];
    } catch (error) {
      if (error.response?.status === 404) {
        deviceUnits.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải đơn vị thiết bị");
      }
    } finally {
      isLoading.value = false;
    }
  };

  const deleteDeviceUnit = async (unitId) => {
    if (!confirm("Bạn chắc chắn muốn xóa đơn vị thiết bị này?")) return false;

    try {
      await deviceUnitService.delete(unitId);
      toast.success("Đã xóa đơn vị thiết bị");
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể xóa đơn vị thiết bị");
      return false;
    }
  };

  return {
    deviceUnits,
    pagination,
    isLoading,
    loadDeviceUnits,
    deleteDeviceUnit,
  };
}
