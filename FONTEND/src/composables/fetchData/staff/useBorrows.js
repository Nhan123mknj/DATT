import { ref, reactive } from "vue";
import { useToast } from "vue-toastification";
import { staffBorrowService } from "../../../services/staff/staffBorrowService"

export function useBorrows() {
    const borrows = ref([]);
    const pagination = reactive({
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
        links: [],
    });
    const isLoading = ref(false);
    const toast = useToast();

    const loadBorrows = async (page = 1, filters = {}) => {
        isLoading.value = true;
        try {
            const params = {
                page,
                status: filters.status ? [filters.status] : undefined,
            };

            const { data } = await staffBorrowService.list(params);
            const payload = data.borrowSlip;
            borrows.value = payload?.data || [];
            pagination.current_page = payload?.current_page || 1;
            pagination.per_page = payload?.per_page || 10;
            pagination.total = payload?.total || 0;
            pagination.last_page = payload?.last_page || 1;
            pagination.links = payload?.links || [];
        } catch (error) {
            if (error.response?.status === 404) {
                borrows.value = [];
                pagination.total = 0;
            } else {
                toast.error("Không thể tải phiếu mượn");
            }
        } finally {
            isLoading.value = false;
        }
    };

    return {
        borrows,
        pagination,
        isLoading,
        loadBorrows,
    };
}