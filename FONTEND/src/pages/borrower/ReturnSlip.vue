<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Phiếu Trả</h1>
        <p class="text-sm text-gray-500 mt-1">
          Theo dõi các thiết bị đang và đã trả.
        </p>
      </div>
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
            @click="handleLoadReturns()"
          >
            <font-awesome-icon icon="filter" class="mr-2" />
            Lọc
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <div v-if="!returns.length" class="text-center py-12">
          <div
            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 text-2xl"
          >
            <font-awesome-icon icon="clipboard-list" />
          </div>
          <p class="text-gray-500 font-medium">Không tìm thấy phiếu trả nào</p>
        </div>
        <div v-else>
          <Table :data="returns" :headers="headers">
            <template #expected_return_date="{ item }">
              <div class="flex items-center gap-2 text-gray-600">
                <font-awesome-icon
                  icon="calendar-day"
                  class="text-gray-400 text-xs"
                />
                {{ formatDate(item.borrow.expected_return_date) }}
              </div>
            </template>
            <template #return_date="{ item }">
              <div class="flex items-center gap-2 text-gray-600">
                <font-awesome-icon icon="clock" class="text-gray-400 text-xs" />
                {{ formatDate(item.return_date) }}
              </div>
            </template>
            <template #returned_by_staff="{ item }">
              <div class="flex items-center gap-2 text-gray-600">
                {{ item.returned_by_staff?.name || "Không rõ" }}
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
              @page-changed="handleLoadReturns"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- <BorrowDetailModal
      :show="showDetailModal"
      :borrow="selectedBorrow"
      @close="closeDetail"
      @open-report="openReport"
    />

    <ReportDeviceModal
      v-model:visible="showReportModal"
      :device-unit="selectedDeviceUnit"
      @success="handleLoadBorrows"
    /> -->
    <ReturnDetailModal
      :show="showDetailModal"
      :return="selectedReturn"
      @close="closeDetail"
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
import { useReturn } from "../../composables/fetchData/borrower/useReturn";
import apiClient from "../../services/api/apiClient";
import ReturnDetailModal from "../../components/borrower/returns/ReturnDetailModal.vue";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";
// import { use } from "react";

export default {
  name: "ReturnSlip",
  components: {
    Table,
    LoadingSkeleton,
    Pagination,
    RouterLink,
    ReturnDetailModal,
  },
  setup() {
    const { statusReverseLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();
    const { loadReturnSlip, returns, pagination, isLoading } = useReturn();
    // const { borrows, pagination, isLoading } = storeToRefs(borrowStore);

    const filters = reactive({
      status: "",
    });

    const headers = {
      id: "Mã phiếu",
      expected_return_date: "Trả dự kiến",
      return_date: "Ngày trả thực tế",
      returned_by_staff: "Người nhận thiết bị",
      credit_score_change: "Thay đổi điểm tín dụng",
    };

    const statusMap = {
      pending: "Chờ duyệt",
      approved: "Đã duyệt",
      completed: "Đang mượn",
      overdue: "Quá hạn",
      returned: "Đã trả",
    };

    const showDetailModal = ref(false);
    const selectedReturn = ref(null);

    const openDetail = (returnSlip) => {
      console.log(returnSlip);

      selectedReturn.value = returnSlip;
      showDetailModal.value = true;
    };

    const handleOpenDetail = async (item) => {
      try {
        const response = await apiClient.get(
          `/borrower/return-slips/${item.id}`
        );
        if (response.data) {
          openDetail(response.data);
        }
      } catch (error) {
        toast.error("Không thể tải chi tiết phiếu trả");
        console.error(error);
      }
    };

    const closeDetail = () => {
      showDetailModal.value = false;
      selectedReturn.value = null;
    };

    const handleLoadReturns = (page = 1) => {
      loadReturnSlip(page, filters);
    };

    const resetFilters = () => {
      filters.status = "";
      handleLoadReturns();
    };

    const route = useRoute();
    const toast = useToast();

    onMounted(() => {
      handleLoadReturns();
    });

    // watch(
    //   [() => route.query.id, () => borrows.value],
    //   async ([id, borrowsList]) => {
    //     if (id) {
    //       try {
    //         const borrow = await fetchBorrowById(id);
    //         if (borrow) {
    //           openDetail(borrow);
    //         }
    //       } catch (e) {
    //         if (!isLoading.value) {
    //           toast.error("Không tìm thấy phiếu mượn");
    //         }
    //       }
    //     }
    //   },
    //   { immediate: true }
    // );

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
      returns,
      isLoading,
      pagination,
      handleLoadReturns,
      loadReturnSlip,
      showDetailModal,
      selectedReturn,
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
