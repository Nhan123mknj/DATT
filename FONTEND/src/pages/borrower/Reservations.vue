<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Đặt trước thiết bị</h1>
        <p class="text-sm text-gray-500">
          Xem trạng thái và tạo yêu cầu đặt trước mới.
        </p>
      </div>
      <Button color="primary" @click="goToCreate">
        <font-awesome-icon icon="plus" class="mr-2" />
        Tạo đặt trước
      </Button>
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
            @click="loadReservations()"
          >
            Lọc
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="reservations" :headers="headers">
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
            <div class="flex gap-2">
              <button
                class="px-3 py-1 rounded-lg border border-gray-200 text-sm"
                @click="showDetails(item)"
              >
                Xem
              </button>
              <button
                v-if="['pending', 'approved'].includes(item.status)"
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 text-sm"
                @click="cancelReservation(item)"
              >
                Hủy
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

    <Modal
      :show="showDetailModal"
      title="Chi tiết đặt trước"
      @close="closeDetail"
    >
      <div v-if="selectedReservation" class="space-y-3 text-sm text-gray-700">
        <p>
          <span class="font-semibold">Mã yêu cầu:</span> #{{
            selectedReservation.id
          }}
        </p>
        <p>
          <span class="font-semibold">Thời gian:</span>
          {{ formatDate(selectedReservation.reserved_from) }} →
          {{ formatDate(selectedReservation.reserved_until) }}
        </p>
        <p>
          <span class="font-semibold">Trạng thái:</span>
          <span
            class="px-3 py-1 rounded-full text-xs font-medium"
            :class="statusClasses(selectedReservation.status)"
          >
            {{ statusLabel(selectedReservation.status) }}
          </span>
        </p>
        <div>
          <span class="font-semibold">Thiết bị:</span>
          <ul class="mt-2 list-disc list-inside space-y-1">
            <li
              v-for="detail in selectedReservation.details || []"
              :key="detail.id"
            >
              Đơn vị #{{ detail.device_unit_id }} -
              {{ detail.device_unit?.device?.name }}
            </li>
          </ul>
        </div>
        <p>
          <span class="font-semibold">Ghi chú:</span>
          {{ selectedReservation.notes || "Không có" }}
        </p>
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
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import Table from "../../components/Table.vue";
import Button from "../../components/Button.vue";
import LoadingSkeleton from "../../components/LoadingSkeleton.vue";
import Pagination from "../../components/Pagination.vue";
import Modal from "../../components/Modal.vue";
import { reservationsService } from "../../services/reservations/reservationsService";

const router = useRouter();
const toast = useToast();

const headers = {
  reserved_from: "Từ ngày",
  reserved_until: "Đến ngày",
  status: "Trạng thái",
};

const statusMap = {
  pending: "Chờ duyệt",
  approved: "Đã duyệt",
  rejected: "Từ chối",
  cancelled: "Đã hủy",
  completed: "Hoàn thành",
};

const reservations = ref([]);
const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  links: [],
});

const filters = reactive({
  status: "",
});

const isLoading = ref(false);
const showDetailModal = ref(false);
const selectedReservation = ref(null);

const statusLabel = (status) => statusMap[status] || status;
const statusClasses = (status) => {
  switch (status) {
    case "approved":
      return "bg-green-100 text-green-700";
    case "pending":
      return "bg-amber-100 text-amber-700";
    case "rejected":
    case "cancelled":
      return "bg-red-100 text-red-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return "—";
  return new Date(dateStr).toLocaleDateString("vi-VN");
};

const loadReservations = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      status: filters.status ? [filters.status] : undefined,
    };
    const { data } = await reservationsService.listBorrower(params);
    const payload = data.data;
    reservations.value = payload?.data || [];
    pagination.current_page = payload?.current_page || 1;
    pagination.per_page = payload?.per_page || 10;
    pagination.total = payload?.total || 0;
    pagination.last_page = payload?.last_page || 1;
    pagination.links = payload?.links || [];
  } catch (error) {
    if (error.response?.status === 404) {
      reservations.value = [];
      pagination.total = 0;
    } else {
      toast.error("Không thể tải đặt trước");
    }
  } finally {
    isLoading.value = false;
  }
};

const goToCreate = () => {
  router.push({ name: "borrower.reservations.create" });
};

const cancelReservation = async (reservation) => {
  if (!confirm("Bạn chắc chắn muốn hủy yêu cầu này?")) return;
  try {
    await reservationsService.cancelBorrower(reservation.id);
    toast.success("Đã hủy yêu cầu");
    loadReservations(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.message || "Không thể hủy yêu cầu");
  }
};

const showDetails = (reservation) => {
  selectedReservation.value = reservation;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  selectedReservation.value = null;
};

const resetFilters = () => {
  filters.status = "";
  loadReservations();
};

onMounted(() => {
  loadReservations();
});
</script>
