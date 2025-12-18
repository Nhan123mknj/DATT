import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { deviceUnitService } from "../services/admin/deviceUnitService";
import { useDeviceStore } from "./deviceStore";

export const useDeviceUnitStore = defineStore("deviceUnit", () => {
  const toast = useToast();
  const deviceStore = useDeviceStore();

  const units = ref([]);
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
    device_id: "",
    status: "",
  });

  const fetchUnits = async (page = 1, customFilters = {}) => {
    isLoading.value = true;
    try {

      if (Object.keys(customFilters).length > 0) {
        Object.assign(filters, customFilters);
      }

      const params = {
        page,
        search: filters.search || undefined,
        device_id: filters.device_id || undefined,
        status: filters.status || undefined,
      };

      const { data } = await deviceUnitService.list(params);
      const payload = data.data;
    //   console.log(payload);
      
      units.value = payload;
      // console.log(units);
      
      pagination.current_page = payload?.current_page || 1;
      pagination.per_page = payload?.per_page || 10;
      pagination.total = payload?.total || 0;
      pagination.last_page = payload?.last_page || 1;
      pagination.links = payload?.links || [];
    } catch (error) {
      if (error.response?.status === 404) {
        units.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải đơn vị thiết bị");
        console.error(error);
      }
    } finally {
      isLoading.value = false;
    }
  };

  const addUnit = async (unitData) => {
    try {
      const { data } = await deviceUnitService.create(unitData);
      toast.success("Thêm đơn vị thiết bị thành công");
      
      if (data.device_unit) {
          deviceStore.updateDeviceUnitCount(data.device_unit.device_id, 1);
      } else if (unitData.device_id) {
          deviceStore.updateDeviceUnitCount(unitData.device_id, 1);
      }
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể thêm đơn vị thiết bị");
      return false;
    }
  };

  const updateUnit = async (id, unitData) => {
    try {
      await deviceUnitService.update(id, unitData);
      toast.success("Cập nhật đơn vị thiết bị thành công");
      // Update local state
      const index = units.value.findIndex(u => u.id === id);
      if (index !== -1) {
          units.value[index] = { ...units.value[index], ...unitData };
      }
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể cập nhật đơn vị thiết bị");
      return false;
    }
  };

  const deleteUnit = async (unitId) => {
    if (!confirm("Bạn chắc chắn muốn xóa đơn vị thiết bị này?")) return false;

    // Find unit to get device_id before deleting
    const unit = units.value.find(u => u.id === unitId);
    const deviceId = unit?.device_id;

    try {
      await deviceUnitService.remove(unitId);
      toast.success("Đã xóa đơn vị thiết bị");
      // Remove from local state immediately
      units.value = units.value.filter((u) => u.id !== unitId);
      pagination.total--;

      if (deviceId) {
          deviceStore.updateDeviceUnitCount(deviceId, -1);
      }
      return true;
    } catch (error) {
      toast.error(
        error.response?.data?.message || "Không thể xóa đơn vị thiết bị"
      );
      return false;
    }
  };

  const updateUnitState = (updatedUnit) => {
    const index = units.value.findIndex((u) => u.id === updatedUnit.id);
    if (index !== -1) {
      units.value[index] = { ...units.value[index], ...updatedUnit };
    }
  };

  // Placeholder for real-time listener
  const initializeListener = () => {
    // TODO: Implement Pusher listener here
    // Echo.channel('device-units')
    //   .listen('DeviceUnitUpdated', (e) => updateUnitState(e.unit))
    //   .listen('DeviceUnitDeleted', (e) => { ... })
  };

  return {
    units,
    pagination,
    isLoading,
    filters,
    fetchUnits,
    addUnit,
    updateUnit,
    deleteUnit,
    updateUnitState,
    initializeListener,
    updateUnitStatus: (unitId, status) => {
      const unit = units.value.find((u) => u.id === unitId);
      if (unit) {
        unit.status = status;
      }
    },
  };
});
