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
                @click="openDetail(item)"
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

    <ModalForm
      :show="showDetailModal"
      title="Chi tiết phiếu mượn"
      @close="closeDetail"
    >
      <div v-if="selectedBorrow" class="space-y-5 text-sm text-gray-700">
        <div
          class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100"
        >
          <div>
            <p
              class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
            >
              Mã phiếu
            </p>
            <p class="font-bold text-gray-900 text-lg">
              #{{ selectedBorrow.id }}
            </p>
          </div>
          <div class="text-right">
            <p
              class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
            >
              Trạng thái
            </p>
            <span
              class="px-3 py-1 rounded-full text-xs font-semibold inline-block"
              :class="statusClasses(selectedBorrow.status)"
            >
              {{ statusReverseLabel(selectedBorrow.status) }}
            </span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Ngày mượn</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon
                icon="calendar-check"
                class="text-indigo-500"
              />
              {{ formatDate(selectedBorrow.borrowed_date) }}
            </p>
          </div>
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Trả dự kiến</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon icon="hourglass-end" class="text-amber-500" />
              {{ formatDate(selectedBorrow.expected_return_date) }}
            </p>
          </div>
        </div>

        <div>
          <p class="font-bold text-gray-900 mb-3 flex items-center gap-2">
            <font-awesome-icon icon="boxes" class="text-gray-400" />
            Danh sách thiết bị
          </p>
          <div
            class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden"
          >
            <ul class="divide-y divide-gray-200">
              <li
                v-for="detail in selectedBorrow.details"
                :key="detail.id"
                class="p-3 hover:bg-white transition-colors flex items-center justify-between"
              >
                <div>
                  <span class="font-medium text-gray-900 block">{{
                    detail.device_unit?.device?.name ||
                    "Thiết bị không xác định"
                  }}</span>
                  <span class="text-xs text-gray-500 font-mono"
                    >SN:
                    {{
                      detail.device_unit?.serial_number || detail.device_unit_id
                    }}</span
                  >
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors"
          @click="closeDetail"
        >
          Đóng
        </button>
      </template>
    </ModalForm>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from "vue";
import { RouterLink } from "vue-router";
import { useToast } from "vue-toastification";
import Table from "../../components/common/Table.vue";
import LoadingSkeleton from "../../components/common/LoadingSkeleton.vue";
import Pagination from "../../components/common/Pagination.vue";
// import Modal from "../../components/Modal.vue";
import ModalForm from "../../components/ModalForm.vue";
import { useBorrows } from "../../composables/fetchData/borrower/useBorrows";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";

export default {
  name: "BorrowerBorrows",
  components: {
    Table,
    LoadingSkeleton,
    Pagination,
    // Modal,
    RouterLink,
    ModalForm,
  },
  setup() {
    const { statusReverseLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();
    const { borrows, pagination, isLoading, loadBorrows } = useBorrows();

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
      borrowed: "Đang mượn",
      overdue: "Quá hạn",
      returned: "Đã trả",
    };

    const showDetailModal = ref(false);
    const selectedBorrow = ref(null);

    const openDetail = (borrow) => {
      selectedBorrow.value = borrow;
      showDetailModal.value = true;
    };

    const closeDetail = () => {
      showDetailModal.value = false;
      selectedBorrow.value = null;
    };

    const handleLoadBorrows = (page = 1) => {
      loadBorrows(page, filters);
    };

    const resetFilters = () => {
      filters.status = "";
      handleLoadBorrows();
    };

    onMounted(() => {
      handleLoadBorrows();
    });

    return {
      filters,
      headers,
      statusMap,
      borrows,
      isLoading,
      pagination,
      handleLoadBorrows,
      showDetailModal,
      selectedBorrow,
      openDetail,
      closeDetail,
      resetFilters,
      statusReverseLabel,
      statusClasses,
      formatDate,
    };
  },
};
</script>
