<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Xin chào, {{ currentUser?.name }}
        </h1>
        <p class="text-gray-500 text-sm">
          Theo dõi nhanh yêu cầu đặt trước và phiếu mượn của bạn.
        </p>
      </div>
      <RouterLink
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500"
        :to="{ name: 'borrower.reservations' }"
      >
        <font-awesome-icon icon="calendar-plus" />
        Tạo đặt trước
      </RouterLink>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <StatCard label="Tổng đặt trước" :value="stats.totalReservations">
        <template #icon>
          <font-awesome-icon icon="calendar" />
        </template>
      </StatCard>
      <StatCard label="Đang chờ duyệt" :value="stats.pendingReservations">
        <template #icon>
          <font-awesome-icon icon="hourglass-half" />
        </template>
      </StatCard>
      <StatCard label="Phiếu mượn đang mượn" :value="stats.activeBorrows">
        <template #icon>
          <font-awesome-icon icon="clipboard-check" />
        </template>
      </StatCard>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <section class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <header
          class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
        >
          <div>
            <h2 class="text-lg font-semibold text-gray-900">
              Đặt trước gần đây
            </h2>
            <p class="text-sm text-gray-500">
              Theo dõi trạng thái yêu cầu của bạn
            </p>
          </div>
          <RouterLink
            class="text-sm text-indigo-600"
            :to="{ name: 'borrower.reservations' }"
            >Xem tất cả</RouterLink
          >
        </header>
        <div v-if="reservationLoading" class="p-6 text-sm text-gray-400">
          Đang tải dữ liệu...
        </div>
        <div
          v-else-if="!recentReservations.length"
          class="p-6 text-sm text-gray-400"
        >
          Chưa có đặt trước nào
        </div>
        <ul v-else class="divide-y divide-gray-100">
          <li
            v-for="item in recentReservations"
            :key="item.id"
            class="px-6 py-4 flex flex-col gap-1"
          >
            <div class="flex items-center justify-between">
              <p class="text-sm font-semibold text-gray-900">
                #{{ item.id }} · {{ item.details?.length || 0 }} thiết bị
              </p>
              <span
                class="px-3 py-1 rounded-full text-xs font-medium"
                :class="statusClasses(item.status)"
              >
                {{ statusLabel(item.status) }}
              </span>
            </div>
            <p class="text-xs text-gray-500">
              {{ formatDate(item.reserved_from) }} →
              {{ formatDate(item.reserved_until) }}
            </p>
          </li>
        </ul>
      </section>

      <section class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <header
          class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
        >
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Phiếu mượn</h2>
            <p class="text-sm text-gray-500">Thiết bị đang được bạn mượn</p>
          </div>
          <RouterLink
            class="text-sm text-indigo-600"
            :to="{ name: 'borrower.borrows' }"
            >Chi tiết</RouterLink
          >
        </header>
        <div v-if="borrowLoading" class="p-6 text-sm text-gray-400">
          Đang tải dữ liệu...
        </div>
        <div
          v-else-if="!activeBorrowSlips.length"
          class="p-6 text-sm text-gray-400"
        >
          Không có phiếu mượn nào
        </div>
        <ul v-else class="divide-y divide-gray-100">
          <li
            v-for="borrow in activeBorrowSlips"
            :key="borrow.id"
            class="px-6 py-4 flex flex-col gap-1"
          >
            <p class="text-sm font-semibold text-gray-900">
              Phiếu #{{ borrow.id }}
            </p>
            <p class="text-xs text-gray-500">
              Trả dự kiến: {{ formatDate(borrow.expected_return_date) }}
            </p>
          </li>
        </ul>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useToast } from "vue-toastification";
import { RouterLink } from "vue-router";
import StatCard from "../../components/StatCard.vue";
import { reservationsService } from "../../services/reservations/reservationsService";
import { borrowService } from "../../services/borrows/borrowService";
import authService from "../../services/auth/authService";

const toast = useToast();
const currentUser = authService.getCurrentUser();

const stats = ref({
  totalReservations: 0,
  pendingReservations: 0,
  activeBorrows: 0,
});

const recentReservations = ref([]);
const activeBorrowSlips = ref([]);
const reservationLoading = ref(false);
const borrowLoading = ref(false);

const formatDate = (value) => {
  if (!value) return "Chưa cập nhật";
  return new Date(value).toLocaleDateString("vi-VN");
};

const statusLabel = (status) => {
  const map = {
    pending: "Chờ duyệt",
    approved: "Đã duyệt",
    rejected: "Từ chối",
    cancelled: "Đã hủy",
    completed: "Hoàn thành",
  };
  return map[status] || status;
};

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

const fetchReservations = async () => {
  reservationLoading.value = true;
  try {
    const { data } = await reservationsService.listBorrower({ per_page: 5 });
    const payload = data.data;
    recentReservations.value = payload?.data || [];
    stats.value.totalReservations = payload?.total || 0;
    stats.value.pendingReservations = recentReservations.value.filter(
      (item) => item.status === "pending"
    ).length;
  } catch (error) {
    if (error.response?.status === 404) {
      recentReservations.value = [];
      stats.value.totalReservations = 0;
      stats.value.pendingReservations = 0;
    } else {
      toast.error("Không thể tải đặt trước");
    }
  } finally {
    reservationLoading.value = false;
  }
};

const fetchBorrows = async () => {
  borrowLoading.value = true;
  try {
    const { data } = await borrowService.list({ per_page: 5 });
    const payload = data.borrowSlip;
    activeBorrowSlips.value = payload?.data || [];
    stats.value.activeBorrows = activeBorrowSlips.value.length;
  } catch (error) {
    if (error.response?.status === 404) {
      activeBorrowSlips.value = [];
      stats.value.activeBorrows = 0;
    } else {
      toast.error("Không thể tải phiếu mượn");
    }
  } finally {
    borrowLoading.value = false;
  }
};

onMounted(() => {
  fetchReservations();
  fetchBorrows();
});
</script>
