<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Quản lý Bảo trì Thiết bị
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Theo dõi và quản lý lịch sử bảo trì, sửa chữa thiết bị
        </p>
      </div>
      <button
        @click="openCreate"
        class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium"
      >
        <font-awesome-icon icon="plus" class="mr-2" />
        Tạo phiếu bảo trì
      </button>
    </div>

    <!-- Filters -->
    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4"
    >
      <div class="grid gap-3 md:grid-cols-4">
        <SearchBar @handleSearch="handleSearch" />

        <Dropdown
          v-model="filters.status"
          label="Trạng thái"
          :options="statusOptions"
          nameKey="label"
          idKey="value"
        />

        <Dropdown
          v-model="filters.type"
          label="Loại"
          :options="typeOptions"
          nameKey="label"
          idKey="value"
        />

        <Dropdown
          v-model="filters.priority"
          label="Mức độ"
          :options="priorityOptions"
          nameKey="label"
          idKey="value"
        />
      </div>

      <div class="flex gap-2">
        <Button @click="resetFilters" label="Đặt lại" color="gray" />
        <Button @click="loadData()" label="Lọc" />
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
      <TableLoading v-if="isLoading" />
      <div v-else>
        <Table :data="maintenances" :headers="headers">
          <template #device_unit="{ item }">
            <div>
              <p class="text-sm font-medium text-gray-900">
                {{ item.device_unit?.device?.name || "N/A" }}
              </p>
              <p class="text-xs text-gray-500">
                SN: {{ item.device_unit?.serial_number || "N/A" }}
              </p>
            </div>
          </template>

          <template #type="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="typeClass(item.type)"
            >
              {{ typeLabel(item.type) }}
            </span>
          </template>

          <template #priority="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="priorityClass(item.priority)"
            >
              {{ priorityLabel(item.priority) }}
            </span>
          </template>

          <template #status="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="statusClass(item.status)"
            >
              {{ statusLabel(item.status) }}
            </span>
          </template>

          <template #reporter="{ item }">
            <span class="text-sm text-gray-600">
              {{ item.reporter?.name || "N/A" }}
            </span>
          </template>

          <template #actions="{ item }">
            <div class="flex gap-2">
              <button
                v-if="
                  item.status === 'pending' || item.status === 'in_progress'
                "
                @click="completeMaintenance(item.id)"
                class="px-3 py-1 rounded-lg border border-green-200 text-green-600 hover:bg-green-50 text-sm font-medium"
              >
                Xử lý xong
              </button>
              <button
                v-if="
                  item.status === 'pending' || item.status === 'in_progress'
                "
                @click="openRetireFromMaintenance(item)"
                class="px-3 py-1 rounded-lg border border-orange-200 text-orange-600 hover:bg-orange-50 text-sm font-medium"
              >
                Thanh lý
              </button>
              <span v-else class="px-3 py-1 text-sm text-gray-400">
                {{ statusLabel(item.status) }}
              </span>
            </div>
          </template>
        </Table>

        <Pagination
          v-if="pagination.total && pagination.last_page > 1"
          :links="pagination.links"
          @page-changed="loadData"
        />
      </div>
    </div>

    <MaintenanceFormModal
      :show="showModal"
      :mode="modalMode"
      :maintenance="selectedMaintenance"
      @close="closeModal"
      @refresh="loadData(pagination.current_page)"
    />

    <RetireModal
      :show="showRetireModal"
      :device-unit="selectedMaintenanceForRetire?.device_unit"
      @close="showRetireModal = false"
      @retire="handleRetireFromMaintenance"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useToast } from "vue-toastification";
import maintenanceService from "../../../services/maintenanceService";
import Table from "../../../components/common/Table.vue";
import Button from "../../../components/common/Button.vue";
import TableLoading from "../../../components/common/TableLoading.vue";
import Pagination from "../../../components/common/Pagination.vue";
import SearchBar from "../../../components/common/SearchBar.vue";
import Dropdown from "../../../components/common/Dropdown.vue";
import MaintenanceFormModal from "../../../components/maintenance/MaintenanceFormModal.vue";
import RetireModal from "../../../components/admin/device_unit/RetireModal.vue";

const toast = useToast();
const isLoading = ref(false);
const maintenances = ref([]);
const showModal = ref(false);
const modalMode = ref("create");
const selectedMaintenance = ref(null);

const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  links: [],
});

const filters = reactive({
  search: "",
  status: "",
  type: "",
  priority: "",
});

const headers = {
  device_unit: "Thiết bị",
  type: "Loại",
  priority: "Mức độ",
  status: "Trạng thái",
  reporter: "Người báo cáo",
};

const statusOptions = [
  { value: "", label: "Tất cả" },
  { value: "pending", label: "Chờ xử lý" },
  { value: "in_progress", label: "Đang xử lý" },
  { value: "completed", label: "Hoàn thành" },
  { value: "cancelled", label: "Đã hủy" },
];

const typeOptions = [
  { value: "", label: "Tất cả" },
  { value: "routine", label: "Bảo trì định kỳ" },
  { value: "repair", label: "Sửa chữa" },
  { value: "inspection", label: "Kiểm tra" },
  { value: "damage_report", label: "Báo hỏng" },
];

const priorityOptions = [
  { value: "", label: "Tất cả" },
  { value: "low", label: "Thấp" },
  { value: "normal", label: "Bình thường" },
  { value: "high", label: "Cao" },
  { value: "urgent", label: "Khẩn cấp" },
];

const loadData = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      search: filters.search || undefined,
      status: filters.status || undefined,
      type: filters.type || undefined,
      priority: filters.priority || undefined,
    };

    const { data } = await maintenanceService.getAll(params);
    maintenances.value = data.data || [];
    pagination.current_page = data.current_page || 1;
    pagination.per_page = data.per_page || 10;
    pagination.total = data.total || 0;
    pagination.last_page = data.last_page || 1;
    pagination.links = data.links || [];
  } catch (error) {
    toast.error("Không thể tải danh sách bảo trì");
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

const openCreate = () => {
  selectedMaintenance.value = null;
  modalMode.value = "create";
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedMaintenance.value = null;
};

const completeMaintenance = async (id) => {
  if (
    !confirm(
      "Xác nhận hoàn thành bảo trì? Thiết bị sẽ chuyển về trạng thái khả dụng."
    )
  )
    return;

  try {
    await maintenanceService.complete(id);
    toast.success("Đã hoàn thành bảo trì. Thiết bị đã sẵn sàng sử dụng.");
    loadData(pagination.current_page);
  } catch (error) {
    toast.error(
      error.response?.data?.message || "Không thể hoàn thành bảo trì"
    );
  }
};

const showRetireModal = ref(false);
const selectedMaintenanceForRetire = ref(null);

const openRetireFromMaintenance = (maintenance) => {
  selectedMaintenanceForRetire.value = maintenance;
  showRetireModal.value = true;
};

const handleRetireFromMaintenance = async (retirementData) => {
  if (!selectedMaintenanceForRetire.value?.device_unit_id) {
    toast.error("Không tìm thấy thiết bị");
    return;
  }

  try {
    const { deviceUnitService } = await import(
      "../../../services/admin/deviceUnitService"
    );
    await deviceUnitService.retire(
      selectedMaintenanceForRetire.value.device_unit_id,
      retirementData
    );

    await maintenanceService.delete(selectedMaintenanceForRetire.value.id);

    toast.success("Đã thanh lý thiết bị");
    showRetireModal.value = false;
    selectedMaintenanceForRetire.value = null;
    loadData(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.error || "Không thể thanh lý thiết bị");
  }
};

const handleSearch = (data) => {
  filters.search = data;
  loadData();
};

const resetFilters = () => {
  filters.search = "";
  filters.status = "";
  filters.type = "";
  filters.priority = "";
  loadData();
};

const typeLabel = (type) => {
  const option = typeOptions.find((o) => o.value === type);
  return option ? option.label : type;
};

const typeClass = (type) => {
  const classes = {
    routine: "bg-blue-100 text-blue-700",
    repair: "bg-orange-100 text-orange-700",
    inspection: "bg-purple-100 text-purple-700",
    damage_report: "bg-red-100 text-red-700",
  };
  return classes[type] || "bg-gray-100 text-gray-700";
};

const priorityLabel = (priority) => {
  const option = priorityOptions.find((o) => o.value === priority);
  return option ? option.label : priority;
};

const priorityClass = (priority) => {
  const classes = {
    low: "bg-gray-100 text-gray-700",
    normal: "bg-blue-100 text-blue-700",
    high: "bg-orange-100 text-orange-700",
    urgent: "bg-red-100 text-red-700",
  };
  return classes[priority] || "bg-gray-100 text-gray-700";
};

const statusLabel = (status) => {
  const option = statusOptions.find((o) => o.value === status);
  return option ? option.label : status;
};

const statusClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-700",
    in_progress: "bg-blue-100 text-blue-700",
    completed: "bg-green-100 text-green-700",
    cancelled: "bg-gray-100 text-gray-700",
  };
  return classes[status] || "bg-gray-100 text-gray-700";
};

const formatCurrency = (amount) => {
  if (!amount) return "0 ₫";
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
};

onMounted(() => {
  loadData();
});
</script>
