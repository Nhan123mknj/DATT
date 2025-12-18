<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Quản lý bảo trì</h1>
        <p class="text-sm text-gray-500">
          Theo dõi và xử lý các yêu cầu bảo trì thiết bị
        </p>
      </div>
      <div class="flex gap-2">
        <select
          v-model="filters.status"
          class="w-[150px] rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
          <option :value="undefined">Tất cả trạng thái</option>
          <option value="pending">Chờ xử lý</option>
          <option value="in_progress">Đang xử lý</option>
          <option value="completed">Hoàn thành</option>
        </select>
        <button
          @click="fetchData"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Làm mới
        </button>
      </div>
    </div>

    <div
      class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
    >
      <Table :data="data" :headers="headers">
        <template #device="{ item }">
          <div>
            <div class="font-medium">
              {{ item.device_unit?.device?.device_name }}
            </div>
            <div class="text-xs text-gray-500">
              SN: {{ item.device_unit?.serial_number }}
            </div>
          </div>
        </template>

        <template #reporter="{ item }">
          <div class="flex items-center gap-2">
            <img
              v-if="item.reporter?.avatar_url"
              :src="item.reporter.avatar_url"
              class="w-6 h-6 rounded-full object-cover"
              alt="Avatar"
            />
            <div
              v-else
              class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600"
            >
              {{ item.reporter?.name?.charAt(0) }}
            </div>
            <span>{{ item.reporter?.name }}</span>
          </div>
        </template>

        <template #priority="{ item }">
          <span
            class="px-2 py-1 rounded-full text-xs font-semibold"
            :class="getPriorityColorClass(item.priority)"
          >
            {{ getPriorityLabel(item.priority) }}
          </span>
        </template>

        <template #status="{ item }">
          <span
            class="px-2 py-1 rounded-full text-xs font-semibold"
            :class="getStatusColorClass(item.status)"
          >
            {{ getStatusLabel(item.status) }}
          </span>
        </template>

        <template #actions="{ item }">
          <button
            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
            @click="openUpdateModal(item)"
          >
            Cập nhật
          </button>
        </template>
      </Table>
    </div>

    <Pagination
      v-if="pagination.total > pagination.per_page"
      :links="pagination.links"
      @page-changed="handlePageChange"
    />

    <UpdateMaintenanceModal
      v-model:visible="showUpdateModal"
      :maintenance="selectedMaintenance"
      @success="fetchData"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import maintenanceService from "../../../services/maintenanceService";
import UpdateMaintenanceModal from "../../../components/maintenance/UpdateMaintenanceModal.vue";
import Table from "../../../components/common/Table.vue";
import Pagination from "../../../components/common/Pagination.vue";

const loading = ref(false);
const data = ref([]);
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  links: [],
});

const filters = reactive({
  status: undefined,
});

const showUpdateModal = ref(false);
const selectedMaintenance = ref(null);

const headers = {
  device: "Thiết bị",
  reporter: "Người báo",
  description: "Mô tả",
  priority: "Mức độ",
  status: "Trạng thái",
  created_at: "Ngày báo",
};

const fetchData = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page: page,
      per_page: pagination.pageSize,
      status: filters.status,
    };
    const response = await maintenanceService.getAll(params);
    data.value = response.data.data;
    pagination.total = response.data.total;
    pagination.current = response.data.current_page;
    pagination.links = response.data.links;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchData(page);
};

const openUpdateModal = (record) => {
  selectedMaintenance.value = record;
  showUpdateModal.value = true;
};

const getPriorityColorClass = (priority) => {
  const map = {
    low: "bg-blue-100 text-blue-800",
    normal: "bg-green-100 text-green-800",
    high: "bg-orange-100 text-orange-800",
    urgent: "bg-red-100 text-red-800",
  };
  return map[priority] || "bg-gray-100 text-gray-800";
};

const getPriorityLabel = (priority) => {
  const map = {
    low: "Thấp",
    normal: "Bình thường",
    high: "Cao",
    urgent: "Khẩn cấp",
  };
  return map[priority] || priority;
};

const getStatusColorClass = (status) => {
  const map = {
    pending: "bg-yellow-100 text-yellow-800",
    in_progress: "bg-blue-100 text-blue-800",
    completed: "bg-green-100 text-green-800",
    cancelled: "bg-gray-100 text-gray-800",
  };
  return map[status] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status) => {
  const map = {
    pending: "Chờ xử lý",
    in_progress: "Đang xử lý",
    completed: "Hoàn thành",
    cancelled: "Hủy bỏ",
  };
  return map[status] || status;
};

onMounted(() => {
  fetchData();
});
</script>
