import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { reservationsService } from "../services/borrower/reservationsService";
import { useDeviceUnitStore } from "./deviceUnitStore";

export const useReservationStore = defineStore("reservation", () => {
  const toast = useToast();
  const deviceUnitStore = useDeviceUnitStore();

  const reservations = ref([]);
  const pagination = reactive({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    links: [],
  });
  const isLoading = ref(false);

  const fetchReservations = async (page = 1, filters = {}) => {
    isLoading.value = true;
    try {
      const params = {
        page,
        status: filters.status ? [filters.status] : undefined,
      };

      const { data } = await reservationsService.list(params);
      const payload = data.data;

      reservations.value = payload?.data || [];
      pagination.current_page = payload?.current_page || 1;
      pagination.per_page = payload?.per_page || 10;
      pagination.total = payload?.total || 0;
      pagination.last_page = payload?.last_page || 1;
      pagination.links = payload?.links || [];
    } catch (error) {
      if (error.response?.status === 404) {
        reservations.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải danh sách đặt trước");
        console.error(error);
      }
    } finally {
      isLoading.value = false;
    }
  };

  const createReservation = async (data) => {
    try {
      const response = await reservationsService.create(data);
      toast.success("Tạo yêu cầu đặt trước thành công");
      
      // Update device unit statuses if they are in the deviceUnitStore
      if (data.devices && Array.isArray(data.devices)) {
          data.devices.forEach(device => {
              if (device.device_unit_id) {
                  deviceUnitStore.updateUnitStatus(device.device_unit_id, 'reserved');
              }
          });
      }

      // Refresh list
      fetchReservations(1);
      return response;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể tạo yêu cầu");
      throw error;
    }
  };

  const cancelReservation = async (reservationId) => {
    if (!confirm("Bạn chắc chắn muốn hủy yêu cầu này?")) return false;

    try {
      await reservationsService.cancel(reservationId);
      toast.success("Đã hủy yêu cầu");
      
      // We might want to fetch the reservation details first to know which units to free up, 
      // but for now, just refreshing the list is the primary goal. 
      // Ideally, the backend handles the status update, and we just refresh the UI.
      // If we wanted optimistic UI updates, we'd need to know the units.
      
      fetchReservations(pagination.current_page);
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể hủy yêu cầu");
      return false;
    }
  };

  return {
    reservations,
    pagination,
    isLoading,
    fetchReservations,
    createReservation,
    cancelReservation,
  };
});
