import { ref, reactive } from 'vue';
import { useToast } from 'vue-toastification';

export function useDataTable(options = {}) {
    const {
        fetchData, 
        deleteData, 
        dataKey = 'data', 
        confirmDeleteMsg = 'Bạn có chắc chắn muốn xóa?',
        perPage = 10
    } = options;

    const toast = useToast();
    const items = ref([]);
    const isLoading = ref(false);
    const pagination = reactive({
        current_page: 1,
        per_page: perPage,
        total: 0,
        last_page: 1,
        links: [],
    });

    const loadData = async (page = 1) => {
        isLoading.value = true;
        try {
            const params = { page };
            const response = await fetchData(params);
            
            let payload = response.data;
            if (dataKey && payload[dataKey]) {
                payload = payload[dataKey];
            }
            
            items.value = payload.data || [];
            pagination.current_page = payload.current_page || 1;
            pagination.per_page = payload.per_page || perPage;
            pagination.total = payload.total || 0;
            pagination.last_page = payload.last_page || 1;
            pagination.links = payload.links || [];
            
        } catch (error) {
            if (error.response?.status === 404) {
                items.value = [];
                pagination.total = 0;
            } else {
                 toast.error("Không thể tải dữ liệu");
            }
        } finally {
            isLoading.value = false;
        }
    };

    const deleteItem = async (id) => {
        if (!confirm(confirmDeleteMsg)) return;
        try {
            await deleteData(id);
            toast.success("Đã xóa thành công");
            loadData(pagination.current_page);
        } catch (error) {
            toast.error(error.response?.data?.error || "Không thể xóa");
        }
    };

    return {
        items,
        isLoading,
        pagination,
        loadData,
        deleteItem
    };
}
