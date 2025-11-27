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
                @click="approveReservation(item)"
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
    <Modal
      :show="showDetailModal"
      title="Chi tiết đặt trước"
      @close="closeDetail"
    >
      <div v-if="selectedReservation" class="space-y-3 text-sm text-gray-700">
        <p>
          <span class="font-semibold">Người mượn:</span>
          {{ selectedReservation.user?.name }}
        </p>
        <p>
          <span class="font-semibold">Email:</span>
          {{ selectedReservation.user?.email }}
        </p>
        <p>
          <span class="font-semibold">Thời gian:</span>
          {{ formatDate(selectedReservation.reserved_from) }} →
          {{ formatDate(selectedReservation.reserved_until) }}
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

    <!-- Reject Modal -->
    <Modal
      :show="showRejectModal"
      title="Lý do từ chối"
      @close="closeRejectModal"
    >
      <form class="space-y-3" @submit.prevent="submitReject">
        <textarea
          v-model="rejectReason"
          rows="4"
          class="w-full px-3 py-2 rounded-lg border border-gray-200"
          placeholder="Nhập lý do..."
        ></textarea>
        <p v-if="rejectError" class="text-xs text-red-500">{{ rejectError }}</p>
        <button type="submit" class="hidden" aria-hidden="true"></button>
      </form>
      <template #footer>
        <div class="flex gap-3">
          <button
            type="button"
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
            @click="closeRejectModal"
          >
            Hủy
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-red-600 text-white"
            @click="submitReject"
            :disabled="rejectLoading"
          >
            {{ rejectLoading ? "Đang xử lý..." : "Từ chối" }}
          </button>
        </div>
      </template>
    </Modal>

    <!-- Create Borrow Modal -->
    <Modal
      :show="showCreateBorrowModal"
      title="Tạo phiếu mượn từ đặt trước"
      @close="closeCreateBorrowModal"
      size="large"
    >
      <div v-if="selectedReservationForBorrow" class="space-y-4">
        <!-- Thông tin người mượn -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs mr-2"
              >👤</span
            >
            Thông tin người mượn
          </h3>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-gray-600">Tên:</p>
              <p class="font-medium text-gray-900">
                {{ selectedReservationForBorrow.user?.name }}
              </p>
            </div>
            <div>
              <p class="text-gray-600">Email:</p>
              <p class="font-medium text-gray-900">
                {{ selectedReservationForBorrow.user?.email }}
              </p>
            </div>
            <div>
              <p class="text-gray-600">Điện thoại:</p>
              <p class="font-medium text-gray-900">
                {{ selectedReservationForBorrow.user?.phone || "—" }}
              </p>
            </div>
            <div>
              <p class="text-gray-600">Mã yêu cầu:</p>
              <p class="font-medium text-gray-900">
                #{{ selectedReservationForBorrow.id }}
              </p>
            </div>
          </div>
        </div>

        <!-- Thời gian mượn -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
          <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs mr-2"
              >📅</span
            >
            Thời gian mượn
          </h3>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-gray-600">Từ ngày:</p>
              <p class="font-medium text-gray-900">
                {{ formatDate(selectedReservationForBorrow.reserved_from) }}
              </p>
            </div>
            <div>
              <p class="text-gray-600">Đến ngày:</p>
              <p class="font-medium text-gray-900">
                {{ formatDate(selectedReservationForBorrow.reserved_until) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Danh sách thiết bị -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center text-xs mr-2"
              >📦</span
            >
            Danh sách thiết bị ({{
              selectedReservationForBorrow.details?.length || 0
            }}
            items)
          </h3>
          <div class="space-y-2 max-h-64 overflow-y-auto">
            <div
              v-for="(detail, index) in selectedReservationForBorrow.details ||
              []"
              :key="detail.id"
              class="flex items-start p-3 bg-white rounded-lg border border-green-200"
            >
              <span
                class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600 text-white text-xs font-bold mr-3 flex-shrink-0"
              >
                {{ index + 1 }}
              </span>
              <div class="flex-1">
                <p class="font-medium text-gray-900">
                  {{ detail.device_unit?.device?.name }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  <span class="inline-block bg-gray-100 px-2 py-1 rounded mr-2">
                    Unit #{{ detail.device_unit_id }}
                  </span>
                  <span
                    v-if="detail.device_unit?.code"
                    class="inline-block bg-gray-100 px-2 py-1 rounded"
                  >
                    {{ detail.device_unit.code }}
                  </span>
                </p>
              </div>
            </div>
            <div
              v-if="!selectedReservationForBorrow.details?.length"
              class="text-center py-4 text-gray-500"
            >
              Không có thiết bị
            </div>
          </div>
        </div>

        <!-- Ghi chú -->
        <div
          v-if="selectedReservationForBorrow.notes"
          class="bg-gray-50 border border-gray-200 rounded-lg p-4"
        >
          <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-gray-600 text-white flex items-center justify-center text-xs mr-2"
              >📝</span
            >
            Ghi chú
          </h3>
          <p class="text-sm text-gray-700 italic">
            {{ selectedReservationForBorrow.notes }}
          </p>
        </div>

        <!-- File cam kết -->
        <div
          v-if="selectedReservationForBorrow.commitment_file"
          class="bg-purple-50 border border-purple-200 rounded-lg p-4"
        >
          <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs mr-2"
              >📄</span
            >
            File cam kết trách nhiệm
          </h3>
          <div class="space-y-3">
            <div
              v-if="isPdfFile(selectedReservationForBorrow.commitment_file)"
              class="rounded-lg overflow-hidden border border-purple-200"
            >
              <iframe
                :src="selectedReservationForBorrow.commitment_file"
                class="w-full"
                style="height: 400px"
              ></iframe>
            </div>
            <div
              v-else-if="
                isImageFile(selectedReservationForBorrow.commitment_file)
              "
              class="rounded-lg overflow-hidden border border-purple-200"
            >
              <img
                :src="selectedReservationForBorrow.commitment_file"
                alt="File cam kết"
                class="w-full max-h-96 object-contain"
              />
            </div>
            <div
              v-else
              class="flex items-center gap-3 p-3 bg-white rounded-lg border border-purple-200"
            >
              <span class="text-2xl">📁</span>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">Tệp đính kèm</p>
                <p class="text-xs text-gray-500">
                  {{
                    getFileName(selectedReservationForBorrow.commitment_file)
                  }}
                </p>
              </div>
              <a
                :href="selectedReservationForBorrow.commitment_file"
                target="_blank"
                class="px-3 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700"
              >
                Tải
              </a>
            </div>
          </div>
        </div>

        <!-- Warning -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-sm text-red-700 flex items-start">
            <span class="mr-2 mt-0.5">⚠️</span>
            <span
              >Sau khi tạo phiếu mượn, yêu cầu đặt trước sẽ được đánh dấu là
              <strong>Đã hoàn thành</strong></span
            >
          </p>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3">
          <button
            type="button"
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
            @click="closeCreateBorrowModal"
          >
            Hủy
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50"
            @click="submitCreateBorrow"
            :disabled="borrowLoading"
          >
            {{ borrowLoading ? "Đang tạo..." : "Xác nhận tạo phiếu mượn" }}
          </button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useToast } from "vue-toastification";
import Table from "../../components/Table.vue";
import LoadingSkeleton from "../../components/LoadingSkeleton.vue";
import Pagination from "../../components/Pagination.vue";
import Modal from "../../components/Modal.vue";
import { reservationsService } from "../../services/reservations/reservationsService";

const toast = useToast();

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

const reservations = ref([]);
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
const selectedReservation = ref(null);

const showRejectModal = ref(false);
const rejectTarget = ref(null);
const rejectReason = ref("");
const rejectError = ref("");
const rejectLoading = ref(false);

const showCreateBorrowModal = ref(false);
const selectedReservationForBorrow = ref(null);
const borrowLoading = ref(false);

const formatDate = (value) => {
  if (!value) return "—";
  return new Date(value).toLocaleDateString("vi-VN");
};

const isPdfFile = (filePath) => {
  return filePath?.toLowerCase().endsWith(".pdf");
};

const isImageFile = (filePath) => {
  if (!filePath) return false;
  const imageExtensions = [".jpg", ".jpeg", ".png", ".gif", ".webp"];
  return imageExtensions.some((ext) => filePath.toLowerCase().endsWith(ext));
};

const getFileName = (filePath) => {
  if (!filePath) return "";
  return filePath.split("/").pop();
};

const statusLabel = (status) => statusMap[status] || status;
const statusClasses = (status) => {
  switch (status) {
    case "approved":
      return "bg-green-100 text-green-700";
    case "pending":
      return "bg-amber-100 text-amber-700";
    case "rejected":
      return "bg-red-100 text-red-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

const loadReservations = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      status: filters.status ? [filters.status] : undefined,
      per_page: pagination.per_page,
    };
    const { data } = await reservationsService.listStaff(params);
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
      toast.error("Không thể tải danh sách");
    }
  } finally {
    isLoading.value = false;
  }
};

const openDetail = (reservation) => {
  selectedReservation.value = reservation;
  showDetailModal.value = true;
};

const closeDetail = () => {
  selectedReservation.value = null;
  showDetailModal.value = false;
};

const approveReservation = async (reservation) => {
  if (!confirm("Xác nhận duyệt yêu cầu này?")) return;
  try {
    await reservationsService.approve(reservation.id);
    toast.success("Đã duyệt yêu cầu");
    loadReservations(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.message || "Duyệt thất bại");
  }
};

const openRejectModal = (reservation) => {
  rejectTarget.value = reservation;
  rejectReason.value = "";
  rejectError.value = "";
  showRejectModal.value = true;
};

const closeRejectModal = () => {
  showRejectModal.value = false;
  rejectTarget.value = null;
};

const submitReject = async () => {
  if (!rejectReason.value.trim()) {
    rejectError.value = "Vui lòng nhập lý do";
    return;
  }
  rejectLoading.value = true;
  rejectError.value = "";
  try {
    await reservationsService.reject(rejectTarget.value.id, {
      reason: rejectReason.value,
    });
    toast.success("Đã từ chối yêu cầu");
    closeRejectModal();
    loadReservations(pagination.current_page);
  } catch (error) {
    rejectError.value = error.response?.data?.message || "Không thể từ chối";
  } finally {
    rejectLoading.value = false;
  }
};

const createBorrow = async (reservation) => {
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
  try {
    console.log(
      "Creating borrow for reservation ID:",
      selectedReservationForBorrow.value
    );
    await reservationsService.createBorrow(
      selectedReservationForBorrow.value.id
    );
    toast.success("Đã tạo phiếu mượn thành công");
    closeCreateBorrowModal();
    loadReservations(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.message || "Không thể tạo phiếu mượn");
    console.error(error);
  } finally {
    borrowLoading.value = false;
  }
};

onMounted(() => {
  loadReservations();
});
</script>
