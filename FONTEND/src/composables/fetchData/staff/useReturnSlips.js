import { ref, computed } from 'vue'
import { returnSlipService } from '../../../services/staff/returnSlipService'
import { useToast } from 'vue-toastification'

export function useReturnSlips() {
  const toast = useToast()
  const returnSlips = ref([])
  const isLoading = ref(false)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  const filters = ref({
    borrow_id: '',
    staff_id: '',
    from_date: '',
    to_date: '',
  })

  const loadReturnSlips = async (page = 1) => {
    isLoading.value = true
    try {
      const params = {
        page,
        per_page: pagination.value.per_page,
        ...filters.value,
      }

      const response = await returnSlipService.getAll(params)
      
      returnSlips.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
    } catch (error) {
      toast.error('Không thể tải danh sách phiếu trả')
      console.error('Load return slips error:', error)
    } finally {
      isLoading.value = false
    }
  }

  const applyFilters = () => {
    loadReturnSlips(1)
  }

  const clearFilters = () => {
    filters.value = {
      borrow_id: '',
      staff_id: '',
      from_date: '',
      to_date: '',
    }
    loadReturnSlips(1)
  }

  return {
    returnSlips,
    isLoading,
    pagination,
    filters,
    loadReturnSlips,
    applyFilters,
    clearFilters,
  }
}
