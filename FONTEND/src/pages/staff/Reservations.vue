<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Quản lý đặt trước</h1>
        <p class="text-sm text-gray-500">
          Xem và xử lý yêu cầu của người mượn.
        </p>
      </div>
      <div class="flex gap-2">
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
        <button
          class="px-4 py-2 rounded-lg bg-gray-900 text-white"
          @click="loadReservations()"
        >
          Lọc
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="reservations" :headers="headers">
          <template #borrower="{ item }">
            {{ item.user?.name || "Không rõ" }}
            <p class="text-xs text-gray-500">{{ item.user?.email }}</p>
          </template>
          <template #status="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="statusClasses(item.status)"
            >
              {{ statusLabel(item.status) }}
            </span>
          </template>
          <template #reserved_from="{ item }">
            {{ formatDate(item.reserved_from) }}
          </template>
          <template #reserved_until="{ item }">
            {{ formatDate(item.reserved_until) }}
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
                @click="approveReservation(item.id)"
              >
                Duyệt
              </button>
              <button
                v-if="item.status === 'pending'"
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 text-sm"
                @click="openRejectModal(item)"
              >
                Từ chối
              </button>
              <button
                v-if="item.status === 'approved'"
                class="px-3 py-1 rounded-lg border border-indigo-200 text-indigo-600 text-sm"
                @click="createBorrow(item)"
              >
                Tạo phiếu mượn
              </button>
              <button
                v-if="
                  item.status === 'cancelled' ||
                  item.status === 'rejected' ||
                  item.status === 'completed'
                "
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 text-sm hover:bg-red-50 transition-colors"
                @click="deleteReservation(item.id)"
                :title="`Xóa reservation (status: ${item.status})`"
              >
                Xóa
              </button>
            </div>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total > pagination.per_page"
          :links="pagination.links"
          @page-changed="loadReservations"
        />
      </div>
    </div>

    <!-- Detail Modal -->
    <ReservationDetailModal
      :show="showDetailModal"
      :reservation="selectedReservation"
      @close="closeDetail"
    />

    <!-- Reject Modal -->
    <ReservationRejectModal
      :show="showRejectModal"
      :loading="rejectLoading"
      :error="rejectError"
      @close="closeRejectModal"
      @submit="submitReject"
    />

    <!-- Create Borrow Modal -->
    <ReservationCreateBorrowModal
      :show="showCreateBorrowModal"
      :reservation="selectedReservationForBorrow"
      :loading="borrowLoading"
      @close="closeCreateBorrowModal"
      @submit="submitCreateBorrow"
    />
  </div>
</template>

<script>
import { onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import Table from "../../components/common/Table.vue";
import LoadingSkeleton from "../../components/common/LoadingSkeleton.vue";
import Pagination from "../../components/common/Pagination.vue";
import { reservationsService } from "../../services/staff/reservationsService";
import { useReservations } from "../../composables/fetchData/staff/useReservations";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";
import ReservationDetailModal from "../../components/staff/reservation/ReservationDetailModal.vue";
import ReservationRejectModal from "../../components/staff/reservation/ReservationRejectModal.vue";
import ReservationCreateBorrowModal from "../../components/staff/reservation/ReservationCreateBorrowModal.vue";

export default {
  name: "StaffReservations",
  components: {
    Table,
    LoadingSkeleton,
    Pagination,
    ReservationDetailModal,
    ReservationRejectModal,
    ReservationCreateBorrowModal,
  },
  setup() {
    const route = useRoute();
    const { statusReverseLabel: statusLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();

    const {
      reservations,
      pagination,
      isLoading,
      filters,
      loadReservations,
      approveReservation,
      rejectReservation,
      deleteReservation,
      createBorrow: createBorrowAction,
    } = useReservations();

    const headers = {
      borrower: "Người mượn",
      reserved_from: "Từ ngày",
      reserved_until: "Đến ngày",
      status: "Trạng thái",
    };

    const statusMap = {
      pending: "Chờ duyệt",
      approved: "Đã duyệt",
      rejected: "Từ chối",
      completed: "Hoàn thành",
      cancelled: "Đã hủy",
    };

    const showDetailModal = ref(false);
    const selectedReservation = ref(null);
    const showRejectModal = ref(false);
    const rejectTarget = ref(null);
    const rejectError = ref("");
    const rejectLoading = ref(false);
    const showCreateBorrowModal = ref(false);
    const selectedReservationForBorrow = ref(null);
    const borrowLoading = ref(false);

    const openDetail = (reservation) => {
      selectedReservation.value = reservation;
      showDetailModal.value = true;
    };

    const closeDetail = () => {
      selectedReservation.value = null;
      showDetailModal.value = false;
    };

    const openRejectModal = (reservation) => {
      rejectTarget.value = reservation;
      rejectError.value = "";
      showRejectModal.value = true;
    };

    const closeRejectModal = () => {
      showRejectModal.value = false;
      rejectTarget.value = null;
    };

    const submitReject = async (reason) => {
      if (!reason.trim()) {
        rejectError.value = "Vui lòng nhập lý do";
        return;
      }
      rejectLoading.value = true;
      rejectError.value = "";

      const success = await rejectReservation(rejectTarget.value.id, reason);

      rejectLoading.value = false;
      if (success) {
        closeRejectModal();
      }
    };

    const openCreateBorrowModal = (reservation) => {
      selectedReservationForBorrow.value = reservation;
      showCreateBorrowModal.value = true;
    };

    const closeCreateBorrowModal = () => {
      showCreateBorrowModal.value = false;
      selectedReservationForBorrow.value = null;
      borrowLoading.value = false;
    };

    const submitCreateBorrow = async () => {
      if (!selectedReservationForBorrow.value) return;

      borrowLoading.value = true;
      const success = await createBorrowAction(
        selectedReservationForBorrow.value.id
      );

      borrowLoading.value = false;
      if (success) {
        closeCreateBorrowModal();
      }
    };

    onMounted(() => {
      loadReservations();
    });

    watch(
      [() => route.query.id, () => reservations.value],
      async ([id, reservationsList]) => {
        if (id && reservationsList && reservationsList.length > 0) {
          const reservation = reservationsList.find((r) => r.id == id);
          if (reservation) {
            openDetail(reservation);
          } else {
            try {
              const response = await reservationsService.show(id);
              if (response.data) {
                openDetail(response.data);
              }
            } catch (error) {
              console.error("Failed to load reservation:", error);
            }
          }
        }
      },
      { immediate: true }
    );

    return {
      filters,
      headers,
      statusMap,
      reservations,
      isLoading,
      pagination,
      loadReservations,
      formatDate,
      statusLabel,
      statusClasses,
      showDetailModal,
      selectedReservation,
      openDetail,
      closeDetail,
      approveReservation,
      deleteReservation,
      showRejectModal,
      rejectError,
      rejectLoading,
      openRejectModal,
      closeRejectModal,
      submitReject,
      showCreateBorrowModal,
      selectedReservationForBorrow,
      borrowLoading,
      createBorrow: openCreateBorrowModal,
      closeCreateBorrowModal,
      submitCreateBorrow,
    };
  },
};
</script>
