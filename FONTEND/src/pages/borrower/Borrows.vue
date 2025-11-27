<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Phiếu mượn</h1>
        <p class="text-sm text-gray-500">
          Theo dõi các thiết bị đang và đã mượn.
        </p>
      </div>
      <RouterLink
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:border-indigo-200 hover:text-indigo-600"
        :to="{ name: 'borrower.reservations' }"
      >
        <font-awesome-icon icon="calendar-plus" />
        Tạo đặt trước mới
      </RouterLink>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4"
    >
      <div
        class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between"
      >
        <select
          v-model="filters.status"
          class="px-3 py-2 rounded-lg border border-gray-200 text-sm"
        >
          <option value="">Tất cả trạng thái</option>
          <option
            v-for="(label, value) in statusMap"
            :key="value"
            :value="value"
          >
            {{ label }}
          </option>
        </select>
        <div class="flex gap-2">
          <button
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
            @click="resetFilters"
          >
            Đặt lại
          </button>
          <button
            class="px-4 py-2 rounded-lg bg-gray-900 text-white"
            @click="loadBorrows()"
          >
            Lọc
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="borrows" :headers="headers">
          <template #status="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="statusClasses(item.status)"
            >
              {{ statusLabel(item.status) }}
            </span>
          </template>
          <template #borrowed_date="{ item }">
            {{ formatDate(item.borrowed_date) }}
          </template>
          <template #expected_return_date="{ item }">
            {{ formatDate(item.expected_return_date) }}
          </template>
          <template #actions="{ item }">
            <button
              class="px-3 py-1 rounded-lg border border-gray-200 text-sm"
              @click="openDetail(item)"
            >
              Chi tiết
            </button>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total > pagination.per_page"
          :links="pagination.links"
          @page-changed="loadBorrows"
        />
      </div>
    </div>

    <Modal
      :show="showDetailModal"
      title="Chi tiết phiếu mượn"
      @close="closeDetail"
    >
      <div v-if="selectedBorrow" class="space-y-3 text-sm text-gray-700">
        <p>
          <span class="font-semibold">Mã phiếu:</span> #{{ selectedBorrow.id }}
        </p>
        <p>
          <span class="font-semibold">Trạng thái:</span>
          {{ statusLabel(selectedBorrow.status) }}
        </p>
        <p>
          <span class="font-semibold">Ngày mượn:</span>
          {{ formatDate(selectedBorrow.borrowed_date) }}
        </p>
        <p>
          <span class="font-semibold">Trả dự kiến:</span>
          {{ formatDate(selectedBorrow.expected_return_date) }}
        </p>
        <div>
          <span class="font-semibold">Thiết bị:</span>
          <ul class="mt-2 list-disc list-inside space-y-1">
            <li v-for="detail in selectedBorrow.details" :key="detail.id">
              #{{
                detail.device_unit?.serial_number || detail.device_unit_id
              }}
              - {{ detail.device_unit?.device?.name }}
            </li>
          </ul>
        </div>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 rounded-lg bg-gray-900 text-white"
          @click="closeDetail"
        >
          Đóng
        </button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { RouterLink } from "vue-router";
import { useToast } from "vue-toastification";
import Table from "../../components/Table.vue";
import LoadingSkeleton from "../../components/LoadingSkeleton.vue";
import Pagination from "../../components/Pagination.vue";
import Modal from "../../components/Modal.vue";
import { borrowService } from "../../services/borrows/borrowService";

const toast = useToast();

const headers = {
  id: "Mã phiếu",
  borrowed_date: "Ngày mượn",
  expected_return_date: "Trả dự kiến",
  status: "Trạng thái",
};

const statusMap = {
  pending: "Chờ duyệt",
  approved: "Đã duyệt",
  borrowed: "Đang mượn",
  overdue: "Quá hạn",
  returned: "Đã trả",
};

const borrows = ref([]);
const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  links: [],
});
const filters = reactive({ status: "" });
const isLoading = ref(false);
const showDetailModal = ref(false);
const selectedBorrow = ref(null);

const statusLabel = (status) => statusMap[status] || status;
const statusClasses = (status) => {
  switch (status) {
    case "borrowed":
      return "bg-blue-100 text-blue-700";
    case "pending":
      return "bg-amber-100 text-amber-700";
    case "returned":
      return "bg-green-100 text-green-700";
    case "overdue":
      return "bg-red-100 text-red-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

const formatDate = (value) => {
  if (!value) return "—";
  return new Date(value).toLocaleDateString("vi-VN");
};

const loadBorrows = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      status: filters.status ? [filters.status] : undefined,
    };
    const { data } = await borrowService.list(params);
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

const openDetail = (borrow) => {
  selectedBorrow.value = borrow;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  selectedBorrow.value = null;
};

const resetFilters = () => {
  filters.status = "";
  loadBorrows();
};

onMounted(() => {
  loadBorrows();
});
</script>
