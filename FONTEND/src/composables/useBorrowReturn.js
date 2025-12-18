import { ref } from 'vue'
import { useToast } from 'vue-toastification'
import { staffBorrowService } from '../services/staff/staffBorrowService'

export function useBorrowReturn(loadBorrows, pagination) {
  const toast = useToast()
  const showReturnModal = ref(false)
  const returnTarget = ref(null)
  const returnNotes = ref('')
  const returnOtp = ref('')
  const returnError = ref('')
  const returnLoading = ref(false)

  const returnItems = ref([])

  const openReturnModal = async (borrow) => {
    returnTarget.value = borrow
    returnNotes.value = ''
    returnOtp.value = ''
    returnError.value = ''
    
    // Initialize return items from borrow details
    returnItems.value = borrow.details.map(detail => ({
      device_unit_id: detail.device_unit_id,
      device_name: detail.device_unit?.device?.name,
      serial_number: detail.device_unit?.serial_number,
      condition_at_return: 'good', // Default
      status: detail.status // Keep track of current status
    })).filter(item => ['borrowed', 'pending'].includes(item.status))

    // Send OTP
    try {
      await staffBorrowService.sendReturnOtp(borrow.id)
      toast.success('Mã OTP đã được gửi đến email người mượn')
    } catch (error) {
      toast.error('Không thể gửi OTP')
      returnError.value = 'Không thể gửi OTP'
    }

    showReturnModal.value = true
  }

  const closeReturnModal = () => {
    showReturnModal.value = false
    returnTarget.value = null
    returnItems.value = []
    returnOtp.value = ''
  }

  const submitReturn = async () => {
    if (!returnOtp.value) {
      returnError.value = 'Vui lòng nhập mã OTP'
      return
    }

    returnLoading.value = true
    try {
      await staffBorrowService.return(returnTarget.value.id, {
        otp: returnOtp.value,
        notes: returnNotes.value,
        return_items: returnItems.value.map(item => ({
          device_unit_id: item.device_unit_id,
          condition_at_return: item.condition_at_return,
        })),
      })
      toast.success('Đã xử lý trả thiết bị thành công')
      closeReturnModal()
      loadBorrows(pagination.current_page)
    } catch (error) {
      returnError.value =
        error.response?.data?.message || error.response?.data?.errors?.otp?.[0] || 'Không thể xử lý trả'
    } finally {
      returnLoading.value = false
    }
  }

  return {
    showReturnModal,
    returnTarget,
    returnNotes,
    returnOtp,
    returnError,
    returnLoading,
    returnItems,
    openReturnModal,
    closeReturnModal,
    submitReturn,
  }
}
