import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { reservationsService } from "../../../services/staff/reservationsService";

export function useReservations() {
  const toast = useToast();
  const filters = reactive({ status: "" });
  const reservations = ref([]);
  const isLoading = ref(false);
  const pagination = reactive({
    current_page: 1,
    total: 0,
    per_page: 10,
    last_page: 1,
  });

  const loadReservations = async (page = 1) => {
    isLoading.value = true;
    try {
      const params = {
        page,
        per_page: pagination.per_page,
        status: filters.status ? [filters.status] : undefined,
      };
      const response = await reservationsService.list(params);
      const result = response.data.data;
      reservations.value = result.data;
      pagination.current_page = result.current_page;
      pagination.total = result.total;
      pagination.last_page = result.last_page;
      pagination.per_page = result.per_page;
    } catch (error) {
      console.error(error);
      toast.error("Không thể tải danh sách đặt trước");
    } finally {
      isLoading.value = false;
    }
  };

  const deleteReservation = async (id) => {
    if (!confirm("Xác nhận xóa yêu cầu này?")) return false;
    try {
      await reservationsService.delete(id);
      toast.success("Đã xóa yêu cầu");
      loadReservations(pagination.current_page);
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Xóa thất bại");
      return false;
    }
  };

  const approveReservation = async (reservationId) => {
    if (!confirm("Xác nhận duyệt yêu cầu này?")) return false;
    try {
      await reservationsService.approve(reservationId);
      toast.success("Đã duyệt yêu cầu");
      loadReservations(pagination.current_page);
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Duyệt thất bại");
      return false;
    }
  };

  const rejectReservation = async (reservationId, reason) => {
    try {
      await reservationsService.reject(reservationId, { reason });
      toast.success("Đã từ chối yêu cầu");
      loadReservations(pagination.current_page);
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể từ chối yêu cầu");
      return false;
    }
  };

  const createBorrow = async (reservationId) => {
    try {
      await reservationsService.createBorrow(reservationId);
      toast.success("Đã tạo phiếu mượn thành công");
      loadReservations(pagination.current_page);
      return true;
    } catch (error) {
      toast.error(error.response?.data?.message || "Không thể tạo phiếu mượn");
      return false;
    }
  };

  return {
    reservations,
    pagination,
    isLoading,
    filters,
    loadReservations,
    approveReservation,
    rejectReservation,
    deleteReservation,
    createBorrow,
  };
}
