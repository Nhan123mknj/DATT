import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { reservationsService } from "../../../services/borrower/reservationsService";

export function useReservations() {
  const toast = useToast();

  const reservations = ref([]);
  const pagination = reactive({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    links: [],
  });
  const isLoading = ref(false);

  const loadReservations = async (page = 1, filters = {}) => {
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
        toast.error("Không thể tải đặt trước");
      }
    } finally {
      isLoading.value = false;
    }
  };

  const cancelReservation = async (reservationId, currentPage = 1) => {
    if (!confirm("Bạn chắc chắn muốn hủy yêu cầu này?")) return false;

    try {
      await reservationsService.cancel(reservationId);
      toast.success("Đã hủy yêu cầu");
      await loadReservations(currentPage);
      return true;
    } catch (error) {
      toast.error(
        error.response?.data?.message || "Không thể hủy yêu cầu"
      );
      return false;
    }
  };

  return {
    reservations,
    pagination,
    isLoading,
    loadReservations,
    cancelReservation,
  };
}
