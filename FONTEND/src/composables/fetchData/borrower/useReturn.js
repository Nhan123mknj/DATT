import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { borrowService } from "../../../services/borrower/borrowService";
import apiClient from "../../../services/api/apiClient";

export function useReturn() {
  const toast = useToast();

  const returns = ref([]);
  const pagination = reactive({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    links: [],
  });
  const isLoading = ref(false);

  const loadReturnSlip = async (page = 1, filters = {}) => {
    isLoading.value = true;
    try {
      const params = {
        page,
        status: filters.status ? [filters.status] : undefined,
      };

      const response = await apiClient.get('/borrower/return-slips', { params });
      const payload = response.data.borrowSlip || response.data;

      returns.value = payload?.data || [];
      pagination.current_page = payload?.current_page || 1;
      pagination.per_page = payload?.per_page || 10;
      pagination.total = payload?.total || 0;
      pagination.last_page = payload?.last_page || 1;
      pagination.links = payload?.links || [];
    } catch (error) {
      if (error.response?.status === 404) {
        returns.value = [];
        pagination.total = 0;
      } else {
        toast.error("Không thể tải phiếu trả");
      }
    } finally {
      isLoading.value = false;
    }
  };

  return {
    returns,
    pagination,
    isLoading,
    loadReturnSlip,
  };
}
