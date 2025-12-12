<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Quản lý phiếu mượn</h1>
        <p class="text-sm text-gray-500">
          Xem và xử lý các phiếu mượn thiết bị.
        </p>
      </div>
      <div class="flex gap-2">
        <select
          v-model="filters.status"
          class="px-3 py-2 rounded-lg border border-gray-200 text-sm"
        >
          <option value="">Tất cả trạng thái</option>
          <option
            v-for="(label, value) in statusBorrowLabel"
            :key="value"
            :value="value"
          >
            {{ label }}
          </option>
        </select>
        <button
          class="px-4 py-2 rounded-lg bg-gray-900 text-white"
          @click="loadBorrows()"
        >
          Lọc
        </button>
        <button
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center gap-2"
          @click="openCreateModal"
        >
          <span>➕</span>
          Tạo phiếu mượn
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="borrows" :headers="headers">
          <template #borrower="{ item }">
            {{ item.borrower?.name || "Không rõ" }}
            <p class="text-xs text-gray-500">{{ item.borrower?.email }}</p>
          </template>
          <template #status="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="statusClasses(item.status)"
            >
              {{ statusBorrowLabel(item.status) }}
            </span>
          </template>
          <template #borrowed_date="{ item }">
            {{ formatDate(item.borrowed_date) }}
          </template>
          <template #expected_return_date="{ item }">
            {{ formatDate(item.expected_return_date) }}
          </template>
          <template #actions="{ item }">
            <div class="flex flex-wrap gap-2">
              <button
                class="px-3 py-1 rounded-lg border border-gray-200 text-sm"
                @click="openDetail(item)"
              >
                Xem
              </button>
              <button
                v-if="item.status === 'pending'"
                class="px-3 py-1 rounded-lg border border-green-200 text-green-700 text-sm"
                @click="approveBorrow(item)"
              >
                Duyệt
              </button>
              <button
                v-if="item.status === 'pending'"
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 text-sm"
                @click="openReject(item)"
              >
                Từ chối
              </button>
              <button
                v-if="
                  item.status === 'approved' ||
                  item.status === 'borrowed' ||
                  item.status === 'overdue'
                "
                class="px-3 py-1 rounded-lg border border-blue-200 text-blue-600 text-sm"
                @click="openReturnModal(item)"
              >
                Xử lý trả
              </button>
            </div>
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

    <BorrowRejectModal
      :show="showModal && modalMode === 'reject'"
      :reason="form.reason"
      :error="errors.reason"
      @update:reason="form.reason = $event"
      @close="closeModal"
      @submit="save(() => loadBorrows(pagination.current_page))"
    />

    <BorrowReturnModal
      :show="showReturnModal"
      :borrow="returnTarget"
      :returnItems="returnItems"
      :signatures="signatures"
      :notes="returnNotes"
      :error="returnError"
      :loading="returnLoading"
      @update:returnItems="returnItems = $event"
      @update:signatures="signatures = $event"
      @update:notes="returnNotes = $event"
      @close="closeReturnModal"
      @submit="submitReturn"
    />

    <BorrowCreateModal
      :show="showCreateModal"
      :form="createForm"
      :errors="createErrors"
      :loading="createLoading"
      :borrowerResults="borrowerResults"
      :categories="categories"
      :tomorrow="tomorrow"
      @close="closeCreateModal"
      @submit="submitCreate"
      @searchBorrowers="searchBorrowers"
      @selectBorrower="selectBorrower"
      @clearBorrower="clearBorrower"
      @addDeviceGroup="addDeviceGroup"
      @removeDeviceGroup="removeDeviceGroup"
      @changeCategory="onCategoryChange"
      @changeDevice="onDeviceChange"
      @updateQuantity="handleConsumableQuantity"
    />
  </div>
</template>

<script>
import { onMounted, reactive, watch, ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useToast } from "vue-toastification";
import Table from "../../components/common/Table.vue";
import LoadingSkeleton from "../../components/common/LoadingSkeleton.vue";
import Pagination from "../../components/common/Pagination.vue";
import BorrowDetailModal from "../../components/staff/borrows/BorrowDetailModal.vue";
import BorrowRejectModal from "../../components/staff/borrows/BorrowRejectModal.vue";
import BorrowReturnModal from "../../components/staff/borrows/BorrowReturnModal.vue";
import BorrowCreateModal from "../../components/staff/borrows/BorrowCreateModal.vue";
import { staffBorrowService } from "../../services/staff/staffBorrowService";
import { deviceService } from "../../services/shared/deviceService";
import { staffUserService } from "../../services/staff/staffUserService";
import { useBorrows } from "../../composables/fetchData/staff/useBorrows";
import { useForm } from "../../composables/useForm";
import { useQuickBorrow } from "../../composables/useQuickBorrow";
import { useBorrowReturn } from "../../composables/useBorrowReturn";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";

export default {
  name: "StaffBorrows",
  components: {
    Table,
    LoadingSkeleton,
    Pagination,
    BorrowDetailModal,
    BorrowRejectModal,
    BorrowReturnModal,
    BorrowCreateModal,
  },
  setup() {
    const toast = useToast();
    const route = useRoute();
    const { statusBorrowLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();

    const filters = reactive({
      status: "",
    });

    const headers = {
      id: "Mã",
      borrower: "Người mượn",
      borrowed_date: "Ngày mượn",
      expected_return_date: "Hạn trả",
      status: "Trạng thái",
    };

    const { borrows, isLoading, pagination, loadBorrows } = useBorrows();

    const {
      form,
      errors,
      showModal,
      modalMode,
      openDetail,
      openReject,
      closeModal,
      save,
    } = useForm({
      rejectData: (id, data) => staffBorrowService.reject(id, data),
      initialForm: {
        id: "",
        reason: "",
        status: "",
        borrower: {},
        details: [],
        borrowed_date: "",
        expected_return_date: "",
        notes: "",
      },
    });

    const {
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
    } = useBorrowReturn(loadBorrows, pagination);
    const {
      showCreateModal,
      createLoading,
      createErrors,
      createForm,
      borrowerResults,
      categories,
      tomorrow,
      openCreateModal,
      closeCreateModal,
      submitCreate,
      searchBorrowers,
      selectBorrower,
      addDeviceGroup,
      removeDeviceGroup,
      onCategoryChange,
      onDeviceChange,
      handleConsumableQuantity,
      clearBorrower,
    } = useQuickBorrow(loadBorrows, pagination);

    const handleLoadBorrows = (page = 1) => {
      loadBorrows(page, filters);
    };

    const approveBorrow = async (borrow) => {
      if (!confirm("Duyệt phiếu mượn này?")) return;
      try {
        await staffBorrowService.approve(borrow.id);
        toast.success("Đã duyệt phiếu mượn");
        handleLoadBorrows(pagination.current_page);
      } catch (error) {
        toast.error("Không thể duyệt phiếu mượn");
      }
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

    return {
      filters,
      headers,
      borrows,
      isLoading,
      pagination,
      handleLoadBorrows,
      statusClasses,
      formatDate,
      form,
      errors,
      showModal,
      modalMode,
      openDetail,
      openReject,
      closeModal,
      save,
      approveBorrow,
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
      showCreateModal,
      createForm,
      createErrors,
      createLoading,
      borrowerResults,
      categories,
      tomorrow,
      openCreateModal,
      closeCreateModal,
      searchBorrowers,
      selectBorrower,
      addDeviceGroup,
      removeDeviceGroup,
      onCategoryChange,
      onDeviceChange,
      handleConsumableQuantity,
      submitCreate,
      statusBorrowLabel,
      clearBorrower,
    };
  },
};
</script>
