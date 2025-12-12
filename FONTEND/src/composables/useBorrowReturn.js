import { ref, reactive } from 'vue'
import { useToast } from 'vue-toastification'
import { staffBorrowService } from '../services/staff/staffBorrowService'
export function useBorrowReturn(loadBorrows, pagination) {
  const toast = useToast()
  const showReturnModal = ref(false)
  const returnTarget = ref(null)
  const returnNotes = ref('')
  const returnError = ref('')
  const returnLoading = ref(false)

  const returnItems = ref([])
  const signatures = reactive({
    borrower: '',
    staff: ''
  })

  const openReturnModal = (borrow) => {
    returnTarget.value = borrow
    returnNotes.value = ''
    returnError.value = ''
    signatures.borrower = ''
    signatures.staff = ''
    
    // Initialize return items from borrow details
    returnItems.value = borrow.details.map(detail => ({
      device_unit_id: detail.device_unit_id,
      device_name: detail.device_unit?.device?.name,
      serial_number: detail.device_unit?.serial_number,
      condition_at_return: 'good', // Default
      status: detail.status // Keep track of current status
    })).filter(item => ['borrowed', 'pending'].includes(item.status)) // Show borrowed or pending items

    showReturnModal.value = true
  }

  const closeReturnModal = () => {
    showReturnModal.value = false
    returnTarget.value = null
    returnItems.value = []
  }

  const submitReturn = async () => {
    if (!signatures.borrower || !signatures.staff) {
      returnError.value = 'Vui lòng ký tên đầy đủ (Người mượn và Nhân viên)'
      return
    }

    returnLoading.value = true
    try {
      await staffBorrowService.return(returnTarget.value.id, {
        notes: returnNotes.value,
        return_items: returnItems.value.map(item => ({
          device_unit_id: item.device_unit_id,
          condition_at_return: item.condition_at_return,
        })),
        signatures: {
          borrower: signatures.borrower,
          staff: signatures.staff
        }
      })
      toast.success('Đã xử lý trả thiết bị và tạo phiếu trả')
      closeReturnModal()
      loadBorrows(pagination.current_page)
    } catch (error) {
      returnError.value =
        error.response?.data?.message || 'Không thể xử lý trả'
    } finally {
      returnLoading.value = false
    }
  }

  return {
    showReturnModal,
    returnTarget,
    returnNotes,
    returnError,
    returnLoading,
    returnItems,
    signatures,
    openReturnModal,
    closeReturnModal,
    submitReturn,
  }
}
