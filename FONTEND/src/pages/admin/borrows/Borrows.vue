<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Quản lý phiếu mượn</h1>
      <p class="text-gray-600 mt-1">
        Xem và theo dõi tất cả phiếu mượn trong hệ thống
      </p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Trạng thái</label
          >
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Tất cả</option>
            <option value="pending">Chờ duyệt</option>
            <option value="approved">Đã duyệt</option>
            <option value="completed">Đang mượn</option>
            <option value="returned">Đã trả</option>
            <option value="overdue">Quá hạn</option>
            <option value="cancelled">Đã hủy</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Từ ngày</label
          >
          <input
            v-model="filters.from_date"
            type="date"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Đến ngày</label
          >
          <input
            v-model="filters.to_date"
            type="date"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Đặt lại
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :headers="headers" :data="borrows">
          <template #borrower="{ item }">
            <div>
              <p class="font-medium text-gray-900">
                {{ item.borrower?.name || "Không rõ" }}
              </p>
              <p class="text-xs text-gray-500">{{ item.borrower?.email }}</p>
            </div>
          </template>
          <template #devices="{ item }">
            <div v-if="item.details && item.details.length > 0">
              <div v-for="detail in item.details" :key="detail.id">
                {{ detail.device_unit?.device?.name }}
              </div>
            </div>
            <div v-else>N/A</div>
          </template>
          <template #borrowed_date="{ item }">
            {{ formatDate(item.borrowed_date) }}
          </template>

          <template #expected_return_date="{ item }">
            {{ formatDate(item.expected_return_date) }}
          </template>

          <template #status="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-semibold"
              :class="statusClasses(item.status)"
            >
              {{ statusBorrowLabel(item.status) }}
            </span>
          </template>

          <template #actions="{ item }">
            <button
              @click="openDetail(item)"
              class="px-3 py-1 rounded-lg border border-gray-200 text-sm hover:bg-gray-50"
            >
              Xem chi tiết
            </button>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total > pagination.per_page"
          :links="pagination.links"
          @page-changed="handleLoadBorrows"
        />
      </div>
    </div>

    <BorrowDetailModal
      :show="showModal && modalMode === 'detail'"
      :borrow="form"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { onMounted, reactive, watch, ref } from "vue";
import { useRoute } from "vue-router";
import Table from "../../../components/common/Table.vue";
import LoadingSkeleton from "../../../components/common/LoadingSkeleton.vue";
import Pagination from "../../../components/common/Pagination.vue";
import BorrowDetailModal from "../../../components/staff/borrows/BorrowDetailModal.vue";
import { staffBorrowService } from "../../../services/staff/staffBorrowService";
import { useBorrows } from "../../../composables/fetchData/staff/useBorrows";
import { useForm } from "../../../composables/useForm";
import useStatusLabel from "../../../composables/utils/statusLabel";
import useFormatDate from "../../../composables/utils/formatDate";

const route = useRoute();
const { statusBorrowLabel, statusClasses } = useStatusLabel();
const { formatDate } = useFormatDate();

const filters = reactive({
  status: "",
  from_date: "",
  to_date: "",
});

const headers = {
  id: "Mã",
  borrower: "Người mượn",
  devices: "Thiết bị",
  borrowed_date: "Ngày mượn",
  expected_return_date: "Hạn trả",
  status: "Trạng thái",
  devices: "Thiết bị",
};

const { borrows, isLoading, pagination, loadBorrows } = useBorrows();

const { form, showModal, modalMode, openDetail, closeModal } = useForm({
  initialForm: {
    id: "",
    status: "",
    borrower: {},
    details: [],
    devices: [],
    borrowed_date: "",
    expected_return_date: "",
    notes: "",
  },
});

const handleLoadBorrows = (page = 1) => {
  loadBorrows(page, filters);
};

const applyFilters = () => {
  handleLoadBorrows(1);
};

const resetFilters = () => {
  filters.status = "";
  filters.from_date = "";
  filters.to_date = "";
  handleLoadBorrows(1);
};

onMounted(() => {
  handleLoadBorrows();
});

watch(
  [() => route.query.id, () => borrows.value],
  async ([id, borrowsList]) => {
    if (id && borrowsList && borrowsList.length > 0) {
      const borrow = borrowsList.find((b) => b.id == id);
      if (borrow) {
        openDetail(borrow);
      } else {
        try {
          const response = await staffBorrowService.show(id);
          if (response.data) {
            openDetail(response.data);
          }
        } catch (error) {
          console.error("Failed to load borrow:", error);
        }
      }
    }
  },
  { immediate: true }
);
</script>
