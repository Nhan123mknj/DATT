<template>
  <div class="space-y-8 max-w-7xl mx-auto">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          Xin chào, {{ currentUser?.name }} 👋
        </h1>
        <p class="text-gray-500 mt-1">
          Chào mừng trở lại! Đây là tổng quan hoạt động của bạn.
        </p>
      </div>
      <div class="flex gap-3">
        <RouterLink
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md"
          :to="{ name: 'borrower.reservations.create' }"
        >
          <font-awesome-icon icon="plus" />
          Tạo yêu cầu mới
        </RouterLink>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-transform hover:-translate-y-1"
      >
        <div
          class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl"
        >
          <font-awesome-icon icon="calendar-check" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Tổng đặt trước</p>
          <p class="text-2xl font-bold text-gray-900">
            {{ stats.totalReservations }}
          </p>
        </div>
      </div>

      <div
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-transform hover:-translate-y-1"
      >
        <div
          class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl"
        >
          <font-awesome-icon icon="clock" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Đang chờ duyệt</p>
          <p class="text-2xl font-bold text-gray-900">
            {{ stats.pendingReservations }}
          </p>
        </div>
      </div>

      <div
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-transform hover:-translate-y-1"
      >
        <div
          class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl"
        >
          <font-awesome-icon icon="laptop" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Đang mượn</p>
          <p class="text-2xl font-bold text-gray-900">
            {{ stats.activeBorrows }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Recent Reservations -->
      <section
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full"
      >
        <header
          class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50"
        >
          <div>
            <h2 class="text-lg font-bold text-gray-900">Đặt trước gần đây</h2>
          </div>
          <RouterLink
            class="text-sm font-medium text-indigo-600 hover:text-indigo-700 hover:underline"
            :to="{ name: 'borrower.reservations' }"
          >
            Xem tất cả
          </RouterLink>
        </header>

        <div class="flex-1 overflow-auto">
          <div v-if="reservationLoading" class="p-8 text-center text-gray-400">
            <font-awesome-icon icon="spinner" spin class="text-2xl mb-2" />
            <p>Đang tải dữ liệu...</p>
          </div>

          <div v-else-if="!recentReservations.length" class="p-12 text-center">
            <div
              class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 text-2xl"
            >
              <font-awesome-icon icon="inbox" />
            </div>
            <p class="text-gray-500 font-medium">Chưa có yêu cầu nào</p>
            <p class="text-gray-400 text-sm mt-1">
              Tạo yêu cầu mới để bắt đầu mượn thiết bị
            </p>
          </div>

          <ul v-else class="divide-y divide-gray-100">
            <li
              v-for="item in recentReservations"
              :key="item.id"
              class="px-6 py-4 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-gray-900">#{{ item.id }}</span>
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="statusClasses(item.status)"
                >
                  {{ statusReverseLabel(item.status) }}
                </span>
              </div>
              <div
                class="flex items-center justify-between text-sm text-gray-500"
              >
                <div class="flex items-center gap-2">
                  <font-awesome-icon
                    icon="calendar-alt"
                    class="text-gray-400"
                  />
                  <span
                    >{{ formatDate(item.reserved_from) }} -
                    {{ formatDate(item.reserved_until) }}</span
                  >
                </div>
                <div class="flex items-center gap-1">
                  <font-awesome-icon icon="box" class="text-gray-400" />
                  <span>{{ item.details?.length || 0 }} thiết bị</span>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </section>

      <section
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full"
      >
        <header
          class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50"
        >
          <div>
            <h2 class="text-lg font-bold text-gray-900">Thiết bị đang mượn</h2>
          </div>
          <RouterLink
            class="text-sm font-medium text-indigo-600 hover:text-indigo-700 hover:underline"
            :to="{ name: 'borrower.borrows' }"
          >
            Chi tiết
          </RouterLink>
        </header>

        <div class="flex-1 overflow-auto">
          <div v-if="borrowLoading" class="p-8 text-center text-gray-400">
            <font-awesome-icon icon="spinner" spin class="text-2xl mb-2" />
            <p>Đang tải dữ liệu...</p>
          </div>

          <div v-else-if="!activeBorrowSlips.length" class="p-12 text-center">
            <div
              class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 text-2xl"
            >
              <font-awesome-icon icon="check-circle" />
            </div>
            <p class="text-gray-500 font-medium">Không có thiết bị đang mượn</p>
            <p class="text-gray-400 text-sm mt-1">
              Bạn hiện không giữ thiết bị nào của hệ thống
            </p>
          </div>

          <ul v-else class="divide-y divide-gray-100">
            <li
              v-for="borrow in activeBorrowSlips"
              :key="borrow.id"
              class="px-6 py-4 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-gray-900"
                  >Phiếu #{{ borrow.id }}</span
                >
                <span
                  class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-lg"
                >
                  Đang mượn
                </span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <font-awesome-icon icon="clock" class="text-gray-400" />
                <span
                  >Hạn trả:
                  <span class="font-medium text-gray-700">{{
                    formatDate(borrow.expected_return_date)
                  }}</span></span
                >
              </div>
            </li>
          </ul>
        </div>
      </section>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              Mã phiếu mượn
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              Các thiêt bị đang mượn {{ borrowedItems.total_devices }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="borrow in borrowedItems.data" :key="borrow.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ borrow.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <ul>
                <li v-for="device in borrow.details" :key="device.id">
                  {{ device.device_unit.serial_number }}
                </li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from "vue";
import { useToast } from "vue-toastification";
import { RouterLink } from "vue-router";
import { reservationsService } from "../../services/borrower/reservationsService";
import { borrowService } from "../../services/borrower/borrowService";
import { useAuthStore } from "../../stores/authStore";
import apiClient from "../../services/api/apiClient";
import { useDataTable } from "../../composables/fetchData/useDataTable";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";

export default {
  name: "BorrowerDashboard",
  components: {
    RouterLink,
  },
  setup() {
    const toast = useToast();
    const { statusReverseLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();
    const authStore = useAuthStore();

    const currentUser = computed(() => authStore.user);
    const borrowedItems = ref([]);
    const stats = reactive({
      totalReservations: 0,
      pendingReservations: 0,
      activeBorrows: 0,
    });
    // const getBorrowedMyList = async () => {
    //   const response = await apiClient.get("/borrower/reports/borrows/me");
    //   borrowedItems.value = response.data.data;
    //   console.log(response.data.data);
    // };
    const getBorrowedMyList = async () => {
      try {
        const response = await apiClient.get(
          "/borrower/dashboard/device-borrows"
        );
        borrowedItems.value = response.data;
        console.log(borrowedItems.value);
      } catch (error) {
        console.error("Failed to load borrowed items:", error);
      }
    };

    const loadStatistics = async () => {
      try {
        const response = await apiClient.get("/borrower/dashboard/statistics");
        if (response.data.success) {
          const data = response.data.data;
          stats.totalReservations = data.reservations.total || 0;
          stats.pendingReservations = data.reservations.pending || 0;
          stats.activeBorrows = data.borrows.active || 0;
        }
        // console.log(response.data);
      } catch (error) {
        console.error("Failed to load statistics:", error);
      }
    };

    const {
      items: recentReservations,
      isLoading: reservationLoading,
      loadData: loadReservations,
      pagination: reservationPagination,
    } = useDataTable({
      fetchData: (params) =>
        reservationsService.list({
          ...params,
          per_page: 5,
        }),
      dataKey: "data",
      perPage: 5,
    });

    const {
      items: activeBorrowSlips,
      isLoading: borrowLoading,
      loadData: loadBorrows,
      pagination: borrowPagination,
    } = useDataTable({
      fetchData: (params) =>
        borrowService.list({
          ...params,
          per_page: 5,
        }),
      dataKey: "borrowSlip",
      perPage: 5,
    });

    onMounted(() => {
      loadStatistics();
      loadReservations();
      loadBorrows();
      getBorrowedMyList();
    });

    return {
      currentUser,
      stats,
      recentReservations,
      activeBorrowSlips,
      reservationLoading,
      borrowLoading,
      formatDate,
      statusReverseLabel,
      statusClasses,
      borrowedItems,
    };
  },
};
</script>
