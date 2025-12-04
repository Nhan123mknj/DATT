<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Đặt trước thiết bị</h1>
        <p class="text-sm text-gray-500 mt-1">
          Xem trạng thái và tạo yêu cầu đặt trước mới.
        </p>
      </div>
      <Button
        color="primary"
        @click="goToCreate"
        class="shadow-lg shadow-indigo-200"
      >
        <font-awesome-icon icon="plus" class="mr-2" />
        Tạo đặt trước
      </Button>
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
            @click="loadReservations()"
          >
            <font-awesome-icon icon="filter" class="mr-2" />
            Lọc
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <div v-if="!reservations.length" class="text-center py-12">
          <div
            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 text-2xl"
          >
            <font-awesome-icon icon="calendar-times" />
          </div>
          <p class="text-gray-500 font-medium">
            Không tìm thấy yêu cầu đặt trước nào
          </p>
        </div>
        <div v-else>
          <Table :data="reservations" :headers="headers">
            <template #status="{ item }">
              <span
                class="px-3 py-1 rounded-full text-xs font-semibold"
                :class="statusClasses(item.status)"
              >
                {{ statusLabel(item.status) }}
              </span>
            </template>
            <template #reserved_from="{ item }">
              <div class="flex items-center gap-2 text-gray-600">
                <font-awesome-icon
                  icon="calendar-day"
                  class="text-gray-400 text-xs"
                />
                {{ formatDate(item.reserved_from) }}
              </div>
            </template>
            <template #reserved_until="{ item }">
              <div class="flex items-center gap-2 text-gray-600">
                <font-awesome-icon
                  icon="calendar-check"
                  class="text-gray-400 text-xs"
                />
                {{ formatDate(item.reserved_until) }}
              </div>
            </template>
            <template #actions="{ item }">
              <div class="flex gap-2">
                <button
                  class="px-3 py-1.5 rounded-lg border border-indigo-200 text-indigo-600 hover:bg-indigo-50 text-sm font-medium transition-colors"
                  @click="showDetails(item)"
                >
                  Chi tiết
                </button>
                <button
                  v-if="['pending', 'approved'].includes(item.status)"
                  class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm font-medium transition-colors"
                  @click="cancelReservation(item)"
                >
                  Hủy
                </button>
              </div>
            </template>
          </Table>
          <div class="mt-6">
            <Pagination
              v-if="pagination.total > pagination.per_page"
              :links="pagination.links"
              @page-changed="loadReservations"
            />
          </div>
        </div>
      </div>
    </div>

    <Modal
      :show="showDetailModal"
      title="Chi tiết đặt trước"
      @close="closeDetail"
      size="large"
    >
      <div v-if="selectedReservation" class="space-y-5 text-sm text-gray-700">
        <!-- Header Info -->
        <div
          class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100"
        >
          <div>
            <p
              class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
            >
              Mã yêu cầu
            </p>
            <p class="font-bold text-gray-900 text-lg">
              #{{ selectedReservation.id }}
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
              :class="statusClasses(selectedReservation.status)"
            >
              {{ statusLabel(selectedReservation.status) }}
            </span>
          </div>
        </div>

        <!-- Time Info -->
        <div class="grid grid-cols-2 gap-4">
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Từ ngày</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon icon="calendar-alt" class="text-indigo-500" />
              {{ formatDate(selectedReservation.reserved_from) }}
            </p>
          </div>
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Đến ngày</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon
                icon="calendar-check"
                class="text-indigo-500"
              />
              {{ formatDate(selectedReservation.reserved_until) }}
            </p>
          </div>
        </div>

        <!-- Devices List -->
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
                v-for="detail in selectedReservation.details || []"
                :key="detail.id"
                class="p-3 hover:bg-white transition-colors flex items-center justify-between"
              >
                <div>
                  <span class="font-medium text-gray-900 block">{{
                    detail.device_unit?.device?.name ||
                    "Thiết bị không xác định"
                  }}</span>
                  <span class="text-xs text-gray-500 font-mono">
                    Unit #{{ detail.device_unit_id }}
                    <span
                      v-if="detail.device_unit?.code"
                      class="ml-2 bg-gray-200 px-1.5 py-0.5 rounded text-gray-600"
                      >{{ detail.device_unit.code }}</span
                    >
                  </span>
                </div>
              </li>
            </ul>
            <div
              v-if="!selectedReservation.details?.length"
              class="p-4 text-center text-gray-500 italic"
            >
              Không có thiết bị nào
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div
          v-if="selectedReservation.notes"
          class="bg-amber-50 border border-amber-100 rounded-xl p-4"
        >
          <p class="font-bold text-amber-800 mb-1 flex items-center gap-2">
            <font-awesome-icon icon="sticky-note" />
            Ghi chú
          </p>
          <p class="text-amber-900">{{ selectedReservation.notes }}</p>
        </div>

        <!-- Commitment File -->
        <div
          v-if="selectedReservation.commitment_file"
          class="bg-blue-50 border border-blue-100 rounded-xl p-4"
        >
          <p class="font-bold text-blue-800 mb-3 flex items-center gap-2">
            <font-awesome-icon icon="file-contract" />
            File cam kết
          </p>

          <div class="space-y-3">
            <div
              v-if="isPdfFile(selectedReservation.commitment_file)"
              class="rounded-lg overflow-hidden border border-blue-200 bg-white"
            >
              <iframe
                :src="selectedReservation.commitment_file"
                class="w-full h-96"
              ></iframe>
            </div>
            <div
              v-else-if="isImageFile(selectedReservation.commitment_file)"
              class="rounded-lg overflow-hidden border border-blue-200 bg-white"
            >
              <img
                :src="selectedReservation.commitment_file"
                alt="File cam kết"
                class="w-full max-h-96 object-contain"
              />
            </div>
            <div
              v-else
              class="flex items-center gap-3 p-3 bg-white rounded-lg border border-blue-200"
            >
              <div
                class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600"
              >
                <font-awesome-icon icon="file-download" class="text-xl" />
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">Tệp đính kèm</p>
                <p class="text-xs text-gray-500">
                  {{ getFileName(selectedReservation.commitment_file) }}
                </p>
              </div>
              <a
                :href="selectedReservation.commitment_file"
                target="_blank"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 transition-colors font-medium"
              >
                Tải xuống
              </a>
            </div>
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
    </Modal>
  </div>
</template>

<script>
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import Table from "../../components/common/Table.vue";
import Button from "../../components/common/Button.vue";
import LoadingSkeleton from "../../components/common/LoadingSkeleton.vue";
import Pagination from "../../components/common/Pagination.vue";
import Modal from "../../components/Modal.vue";
import { reservationsService } from "../../services/borrower/reservationsService";

export default {
  name: "BorrowerReservations",
  components: {
    Table,
    Button,
    LoadingSkeleton,
    Pagination,
    Modal,
  },
  data() {
    return {
      headers: {
        reserved_from: "Từ ngày",
        reserved_until: "Đến ngày",
        status: "Trạng thái",
      },
      statusMap: {
        pending: "Chờ duyệt",
        approved: "Đã duyệt",
        rejected: "Từ chối",
        cancelled: "Đã hủy",
        completed: "Hoàn thành",
      },
      reservations: [],
      pagination: {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
        links: [],
      },
      filters: {
        status: "",
      },
      isLoading: false,
      showDetailModal: false,
      selectedReservation: null,
    };
  },
  mounted() {
    this.loadReservations();
  },
  methods: {
    statusLabel(status) {
      return this.statusMap[status] || status;
    },
    statusClasses(status) {
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
    },
    formatDate(dateStr) {
      if (!dateStr) return "—";
      return new Date(dateStr).toLocaleDateString("vi-VN");
    },
    isPdfFile(filePath) {
      return filePath?.toLowerCase().endsWith(".pdf");
    },
    isImageFile(filePath) {
      if (!filePath) return false;
      const imageExtensions = [".jpg", ".jpeg", ".png", ".gif", ".webp"];
      return imageExtensions.some((ext) =>
        filePath.toLowerCase().endsWith(ext)
      );
    },
    getFileName(filePath) {
      if (!filePath) return "";
      return filePath.split("/").pop();
    },
    async loadReservations(page = 1) {
      this.isLoading = true;
      try {
        const params = {
          page,
          status: this.filters.status ? [this.filters.status] : undefined,
        };
        const { data } = await reservationsService.listBorrower(params);
        const payload = data.data;
        this.reservations = payload?.data || [];
        this.pagination.current_page = payload?.current_page || 1;
        this.pagination.per_page = payload?.per_page || 10;
        this.pagination.total = payload?.total || 0;
        this.pagination.last_page = payload?.last_page || 1;
        this.pagination.links = payload?.links || [];
      } catch (error) {
        if (error.response?.status === 404) {
          this.reservations = [];
          this.pagination.total = 0;
        } else {
          this.toast.error("Không thể tải đặt trước");
        }
      } finally {
        this.isLoading = false;
      }
    },
    goToCreate() {
      this.router.push({ name: "borrower.reservations.create" });
    },
    async cancelReservation(reservation) {
      if (!confirm("Bạn chắc chắn muốn hủy yêu cầu này?")) return;
      try {
        await reservationsService.cancelBorrower(reservation.id);
        this.toast.success("Đã hủy yêu cầu");
        this.loadReservations(this.pagination.current_page);
      } catch (error) {
        this.toast.error(
          error.response?.data?.message || "Không thể hủy yêu cầu"
        );
      }
    },
    showDetails(reservation) {
      this.selectedReservation = reservation;
      this.showDetailModal = true;
    },
    closeDetail() {
      this.showDetailModal = false;
      this.selectedReservation = null;
    },
    resetFilters() {
      this.filters.status = "";
      this.loadReservations();
    },
  },
  setup() {
    const router = useRouter();
    const toast = useToast();
    return { router, toast };
  },
};
</script>
