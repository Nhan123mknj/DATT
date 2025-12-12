import { ref, reactive, computed } from 'vue'
import { useToast } from 'vue-toastification'
import { deviceService } from '../services/shared/deviceService'
import { staffUserService } from '../services/staff/staffUserService'
import { staffBorrowService } from '../services/staff/staffBorrowService'

export function useQuickBorrow(loadBorrows, pagination) {
  const toast = useToast()
  const showCreateModal = ref(false)
  const createLoading = ref(false)
  const createErrors = ref({})
  const borrowerResults = ref([])
  const categories = ref([])
  const searchTimeout = ref(null)

  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  const tomorrowStr = tomorrow.toISOString().split('T')[0]

  const createEmptyDeviceGroup = () => ({
    category_id: '',
    category_type: '',
    device_id: '',
    quantity: 0,
    device_unit_ids: [],
    devices: [],
    deviceUnits: [],
  })

  const createForm = reactive({
    borrower_id: null,
    borrower_name: '',
    borrower_email: '',
    borrower_role: '',
    borrower_search: '',
    deviceGroups: [createEmptyDeviceGroup()],
    expected_return_date: '',
    notes: '',
  })



  const loadCategories = async () => {
    try {
      const { data } = await deviceService.getCategories()
      categories.value = (data.data || []).filter((c) => c.type !== 'expensive')
    } catch (error) {
      console.error('Load categories error:', error)
      toast.error('Không thể tải danh sách loại thiết bị')
    }
  }

  const resetCreateForm = () => {
    createForm.borrower_id = null
    createForm.borrower_name = ''
    createForm.borrower_email = ''
    createForm.borrower_role = ''
    createForm.borrower_search = ''
    createForm.deviceGroups = [createEmptyDeviceGroup()]
    createForm.expected_return_date = ''
    createForm.notes = ''
    createErrors.value = {}
    borrowerResults.value = []
  }

  const searchBorrowers = async () => {
    if (searchTimeout.value) {
      clearTimeout(searchTimeout.value)
    }

    searchTimeout.value = setTimeout(async () => {
      if (!createForm.borrower_search || createForm.borrower_search.length < 2) {
        borrowerResults.value = []
        return
      }

      try {
        const { data } = await staffUserService.search({
          search: createForm.borrower_search,
        })
        borrowerResults.value = data.data || []

        const exactMatch = borrowerResults.value.find(
          (user) =>
            user.code &&
            user.code.toLowerCase() === createForm.borrower_search.toLowerCase()
        )

        if (exactMatch) {
          selectBorrower(exactMatch)
        }
      } catch (error) {
        console.error('Search borrowers error:', error)
        toast.error('Không thể tìm kiếm người mượn')
      }
    }, 300)
  }

  const selectBorrower = (user) => {
    createForm.borrower_id = user.id
    createForm.borrower_name = user.name
    createForm.borrower_email = user.email
    createForm.borrower_role = user.role 
    borrowerResults.value = []
    createErrors.value.borrower_id = ''
  }

  const clearBorrower = () => {
    createForm.borrower_id = null
    createForm.borrower_name = ''
    createForm.borrower_email = ''
    createForm.borrower_role = ''
    createForm.borrower_search = ''
    borrowerResults.value = []
  }

  const addDeviceGroup = () => {
    createForm.deviceGroups.push(createEmptyDeviceGroup())
  }

  const removeDeviceGroup = (index) => {
    if (createForm.deviceGroups.length > 1) {
      createForm.deviceGroups.splice(index, 1)
    }
  }

  const onCategoryChange = async (groupIndex) => {
    const group = createForm.deviceGroups[groupIndex]
    const categoryId = group.category_id

    group.device_id = ''
    group.devices = []
    group.deviceUnits = []
    group.device_unit_ids = []
    group.quantity = 0

    if (!categoryId) {
      group.category_type = ''
      return
    }

    const category = categories.value.find((c) => c.id == categoryId)
    group.category_type = category?.type || 'normal'

    try {
      const { data } = await deviceService.getDevicesByCategory(categoryId)
      group.devices = data.data || []
    } catch (error) {
      console.error('Load devices error:', error)
      toast.error('Không thể tải danh sách thiết bị')
    }
  }

  const onDeviceChange = async (groupIndex) => {
    const group = createForm.deviceGroups[groupIndex]
    const deviceId = group.device_id

    group.deviceUnits = []
    group.device_unit_ids = []
    group.quantity = 0

    if (!deviceId) return

    try {
      const { data } = await deviceService.getDeviceUnitsByDevice(deviceId)
      group.deviceUnits = data.data || []
    } catch (error) {
      console.error('Load device units error:', error)
      toast.error('Không thể tải danh sách đơn vị thiết bị')
    }
  }

  const handleConsumableQuantity = (groupIndex) => {
    const group = createForm.deviceGroups[groupIndex]
    const maxQuantity = group.deviceUnits.length

    if (group.quantity > maxQuantity) {
      group.quantity = maxQuantity
    }

    group.device_unit_ids = group.deviceUnits
      .slice(0, group.quantity)
      .map((u) => u.id)
  }

  const openCreateModal = () => {
    resetCreateForm()
    showCreateModal.value = true
    loadCategories()
  }

  const closeCreateModal = () => {
    showCreateModal.value = false
    resetCreateForm()
    createLoading.value = false
  }

  const submitCreate = async () => {
    createErrors.value = {}

    if (!createForm.borrower_id) {
      createErrors.value.borrower_id = 'Vui lòng chọn người mượn'
      return
    }

    const allDeviceUnits = []
    for (let i = 0; i < createForm.deviceGroups.length; i++) {
      const group = createForm.deviceGroups[i]

      if (!group.category_id) {
        createErrors.value.devices = `Nhóm ${i + 1}: Vui lòng chọn loại thiết bị`
        return
      }

      if (!group.device_id) {
        createErrors.value.devices = `Nhóm ${i + 1}: Vui lòng chọn thiết bị`
        return
      }

      if (group.category_type === 'consumable') {
        if (!group.quantity || group.quantity <= 0) {
          createErrors.value.devices = `Nhóm ${i + 1}: Vui lòng nhập số lượng`
          return
        }
      } else {
        if (!group.device_unit_ids || group.device_unit_ids.length === 0) {
          createErrors.value.devices = `Nhóm ${i + 1}: Vui lòng chọn ít nhất một đơn vị`
          return
        }
      }

      group.device_unit_ids.forEach((unitId) => {
        allDeviceUnits.push({
          device_unit_id: unitId,
          condition_at_borrow: 'good',
        })
      })
    }

    if (allDeviceUnits.length === 0) {
      createErrors.value.devices = 'Vui lòng chọn ít nhất một thiết bị'
      return
    }

    if (!createForm.expected_return_date) {
      createErrors.value.expected_return_date = 'Vui lòng chọn ngày trả dự kiến'
      return
    }



    createLoading.value = true

    try {
      const payload = {
        borrower_id: createForm.borrower_id,
        expected_return_date: createForm.expected_return_date,
        devices: allDeviceUnits,
        notes: createForm.notes,
      }

      await staffBorrowService.create(payload)
      toast.success('Đã tạo phiếu mượn thành công')
      closeCreateModal()
      loadBorrows(pagination.current_page)
    } catch (error) {
      createErrors.value.general =
        error.response?.data?.message || 'Không thể tạo phiếu mượn'
      console.error(error)
    } finally {
      createLoading.value = false
    }
  }

  return {
    showCreateModal,
    createLoading,
    createErrors,
    createForm,
    borrowerResults,
    categories,

    tomorrow: tomorrowStr,

    openCreateModal,
    closeCreateModal,
    submitCreate,
    searchBorrowers,
    selectBorrower,
    clearBorrower,
    addDeviceGroup,
    removeDeviceGroup,
    onCategoryChange,
    onDeviceChange,
    handleConsumableQuantity,
  }
}
