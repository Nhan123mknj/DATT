<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Phiếu mượn</h1>
        <p class="text-sm text-gray-500 mt-1">
          Theo dõi các thiết bị đang và đã mượn.
        </p>
      </div>
      <RouterLink
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md"
        :to="{ name: 'borrower.reservations.create' }"
      >
        <font-awesome-icon icon="plus" />
        Tạo yêu cầu mới
      </RouterLink>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6"
    >
      <div
        class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between bg-gray-50 p-4 rounded-xl border border-gray-200"
      >
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <span class="text-sm font-medium text-gray-700 whitespace-nowrap"
            >Trạng thái:</span
          >
          <select
            v-model="filters.status"
            class="w-full sm:w-48 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm"
          >
            <option value="">Tất cả</option>
            <option
              v-for="(label, value) in statusMap"
              :key="value"
              :value="value"
            >
              {{ label }}
            </option>
          </select>
        </div>
        <div class="flex gap-3 w-full sm:w-auto">
          <button
            class="flex-1 sm:flex-none px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium transition-colors text-sm"
            @click="resetFilters"
          >
            <font-awesome-icon icon="undo" class="mr-2" />
            Đặt lại
          </button>
          <button
            class="flex-1 sm:flex-none px-6 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition-colors text-sm shadow-sm"
            @click="loadBorrows()"
          >
            <font-awesome-icon icon="filter" class="mr-2" />
            Lọc
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <div v-if="!borrows.length" class="text-center py-12">
          <div
            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 text-2xl"
          >
            <font-awesome-icon icon="clipboard-list" />
          </div>
          <p class="text-gray-500 font-medium">Không tìm thấy phiếu mượn nào</p>
        </div>
        <div v-else>
          <Table :data="borrows" :headers="headers">
            <template #status="{ item }">
              <span
                class="px-3 py-1 rounded-full text-xs font-semibold"
                :class="statusClasses(item.status)"
              >
                {{ statusReverseLabel(item.status) }}
              </span>
            </template>
            <template #borrowed_date="{ item }">
              <div class="flex items-center gap-2 text-gray-600">
                <font-awesome-icon
                  icon="calendar-day"
                  class="text-gray-400 text-xs"
                />
                {{ formatDate(item.borrowed_date) }}
              </div>
            </template>
            <template #expected_return_date="{ item }">
              <div class="flex items-center gap-2 text-gray-600">
                <font-awesome-icon icon="clock" class="text-gray-400 text-xs" />
                {{ formatDate(item.expected_return_date) }}
              </div>
            </template>
            <template #actions="{ item }">
              <button
                class="px-3 py-1.5 rounded-lg border border-indigo-200 text-indigo-600 hover:bg-indigo-50 text-sm font-medium transition-colors"
                @click="handleOpenDetail(item)"
              >
                Chi tiết
              </button>
            </template>
          </Table>
          <div class="mt-6">
            <Pagination
              v-if="pagination.total > pagination.per_page"
              :links="pagination.links"
              @page-changed="loadBorrows"
            />
          </div>
        </div>
      </div>
    </div>

    <BorrowDetailModal
      :show="showDetailModal"
      :borrow="selectedBorrow"
      @close="closeDetail"
      @open-report="openReport"
    />

    <ReportDeviceModal
      v-model:visible="showReportModal"
      :device-unit="selectedDeviceUnit"
      @success="handleLoadBorrows"
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from "vue";
import { RouterLink, useRoute } from "vue-router";
import { useToast } from "vue-toastification";
import Table from "../../components/common/Table.vue";
import LoadingSkeleton from "../../components/common/LoadingSkeleton.vue";
import Pagination from "../../components/common/Pagination.vue";
import BorrowDetailModal from "../../components/borrower/borrows/BorrowDetailModal.vue";
import ReportDeviceModal from "../../components/maintenance/ReportDeviceModal.vue";
import { useBorrowStore } from "../../stores/borrowStore";
import { storeToRefs } from "pinia";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";

export default {
  name: "BorrowerBorrows",
  components: {
    Table,
    LoadingSkeleton,
    Pagination,
    RouterLink,
    BorrowDetailModal,
    ReportDeviceModal,
  },
  setup() {
    const { statusReverseLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();

    const borrowStore = useBorrowStore();
    const { borrows, pagination, isLoading } = storeToRefs(borrowStore);
    const { fetchBorrows, fetchBorrowById } = borrowStore;

    const filters = reactive({
      status: "",
    });

    const headers = {
      id: "Mã phiếu",
      borrowed_date: "Ngày mượn",
      expected_return_date: "Trả dự kiến",
      status: "Trạng thái",
    };

    const statusMap = {
      pending: "Chờ duyệt",
      approved: "Đã duyệt",
      completed: "Đang mượn",
      overdue: "Quá hạn",
      returned: "Đã trả",
    };

    const showDetailModal = ref(false);
    const selectedBorrow = ref(null);

    const openDetail = (borrow) => {
      console.log(borrow);

      selectedBorrow.value = borrow;
      showDetailModal.value = true;
    };

    const handleOpenDetail = async (item) => {
      try {
        const borrow = await fetchBorrowById(item.id);
        if (borrow) {
          openDetail(borrow);
        }
      } catch (error) {
        toast.error("Không thể tải chi tiết phiếu mượn");
        console.error(error);
      }
    };

    const closeDetail = () => {
      showDetailModal.value = false;
      selectedBorrow.value = null;
    };

    const handleLoadBorrows = (page = 1) => {
      fetchBorrows(page, filters);
    };

    const loadBorrows = handleLoadBorrows;

    const resetFilters = () => {
      filters.status = "";
      handleLoadBorrows();
    };

    const route = useRoute();
    const toast = useToast();

    onMounted(() => {
      handleLoadBorrows();
    });

    watch(
      [() => route.query.id, () => borrows.value],
      async ([id, borrowsList]) => {
        if (id) {
          try {
            const borrow = await fetchBorrowById(id);
            if (borrow) {
              openDetail(borrow);
            }
          } catch (e) {
            if (!isLoading.value) {
              toast.error("Không tìm thấy phiếu mượn");
            }
          }
        }
      },
      { immediate: true }
    );

    const showReportModal = ref(false);
    const selectedDeviceUnit = ref(null);

    const openReport = (deviceUnit) => {
      selectedDeviceUnit.value = deviceUnit;
      showReportModal.value = true;
    };

    return {
      filters,
      headers,
      statusMap,
      borrows,
      isLoading,
      pagination,
      handleLoadBorrows,
      loadBorrows,
      showDetailModal,
      selectedBorrow,
      openDetail,
      handleOpenDetail,
      closeDetail,
      resetFilters,
      statusReverseLabel,
      statusClasses,
      formatDate,
      showReportModal,
      selectedDeviceUnit,
      openReport,
    };
  },
};
</script>
