<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">
        <font-awesome-icon icon="fa-solid fa-chart-simple" /> Thống Kê & Báo Cáo
      </h1>
      <p class="text-gray-600">Quản lý và phân tích dữ liệu hệ thống</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div
        class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <div
            class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center"
          >
            <span class="text-2xl"
              ><font-awesome-icon icon="fa-solid fa-wrench" />
            </span>
          </div>
        </div>
        <h3 class="text-sm font-medium text-gray-600 mb-1">
          Tổng thiết bị hư hỏng
        </h3>
        <p class="text-3xl font-bold text-gray-900 mb-1">{{ totalDamages }}</p>
        <p class="text-xs text-gray-500">Trong 30 ngày qua</p>
      </div>

      <div
        class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <div
            class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
          >
            <span class="text-2xl"
              ><font-awesome-icon icon="fa-solid fa-microchip" />
            </span>
          </div>
        </div>
        <h3 class="text-sm font-medium text-gray-600 mb-1">
          Số thiết bị còn trong kho
        </h3>
        <p class="text-3xl font-bold text-gray-900 mb-1">{{ totalDevices }}</p>
        <p class="text-xs text-gray-500">Trong 30 ngày qua</p>
      </div>

      <div
        class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <div
            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
          >
            <span class="text-2xl"
              ><font-awesome-icon icon="fa-solid fa-clipboard"
            /></span>
          </div>
        </div>
        <h3 class="text-sm font-medium text-gray-600 mb-1">Tổng hoạt động</h3>
        <p class="text-3xl font-bold text-gray-900 mb-1">
          {{ totalActivities }}
        </p>
        <p class="text-xs text-gray-500">Trong 7 ngày qua</p>
      </div>

      <div
        class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <div
            class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"
          >
            <span class="text-2xl"
              ><font-awesome-icon icon="fa-solid fa-user"
            /></span>
          </div>
        </div>
        <h3 class="text-sm font-medium text-gray-600 mb-1">
          Người dùng hoạt động
        </h3>
        <p class="text-3xl font-bold text-gray-900 mb-1">{{ activeUsers }}</p>
        <p class="text-xs text-gray-500">Trong 7 ngày qua</p>
      </div>
    </div>

    <div>
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Danh mục báo cáo</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          @click="navigateTo('deviceDamage')"
          class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer group"
        >
          <div class="flex items-start justify-between mb-4">
            <div
              class="w-14 h-14 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg"
            >
              <span class="text-3xl">🔧</span>
            </div>
            <svg
              class="w-6 h-6 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Báo cáo hư hỏng thiết bị
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            Xem lịch sử hư hỏng, chi phí sửa chữa, thiết bị hay hỏng nhất
          </p>
          <ul class="space-y-2">
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Lịch sử hư hỏng theo thiết bị
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Thống kê chi phí sửa chữa
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Top thiết bị hay hỏng
            </li>
          </ul>
        </div>

        <div
          @click="navigateTo('activityLogs')"
          class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer group"
        >
          <div class="flex items-start justify-between mb-4">
            <div
              class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg"
            >
              <span class="text-3xl">📋</span>
            </div>
            <svg
              class="w-6 h-6 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Lịch sử hoạt động
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            Theo dõi mọi thay đổi trong hệ thống
          </p>
          <ul class="space-y-2">
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Lịch sử thay đổi dữ liệu
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Audit trail đầy đủ
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Tìm kiếm và lọc
            </li>
          </ul>
        </div>

        <div
          @click="navigateTo('borrowStats')"
          class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer group"
        >
          <div class="flex items-start justify-between mb-4">
            <div
              class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg"
            >
              <span class="text-3xl">📊</span>
            </div>
            <svg
              class="w-6 h-6 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Thống kê mượn trả
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            Phân tích xu hướng và hiệu suất
          </p>
          <ul class="space-y-2">
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Số lượng mượn theo thời gian
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Tỷ lệ trả đúng hạn
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Thiết bị được mượn nhiều nhất
            </li>
          </ul>
        </div>

        <div
          @click="navigateTo('stock')"
          class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer group"
        >
          <div class="flex items-start justify-between mb-4">
            <div
              class="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg"
            >
              <span class="text-3xl">📦</span>
            </div>
            <svg
              class="w-6 h-6 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Báo cáo kho</h3>
          <p class="text-sm text-gray-600 mb-4">
            Thống kê xuất nhập kho thiết bị
          </p>
          <ul class="space-y-2">
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Tổng quan tồn kho
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Lịch sử xuất/nhập kho
            </li>
            <li class="flex items-center text-sm text-gray-600">
              <svg
                class="w-4 h-4 text-green-500 mr-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Thống kê theo tháng
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { reportsService } from "../../../services/admin/reportsService";

const router = useRouter();
const toast = useToast();

const totalDamages = ref(0);
const totalDevices = ref(0);
const totalActivities = ref(0);
const activeUsers = ref(0);
const isLoading = ref(true);

onMounted(async () => {
  await loadStats();
});

const loadStats = async () => {
  try {
    isLoading.value = true;
    const response = await reportsService.getDashboardStats();
    totalDamages.value = response.data.total_damages;
    totalActivities.value = response.data.total_activities;
    activeUsers.value = response.data.active_users;
    totalDevices.value = response.data.total_devices;
  } catch (error) {
    console.error("Error loading dashboard stats:", error);
    toast.error("Không thể tải thống kê");
  } finally {
    isLoading.value = false;
  }
};

const navigateTo = (reportType) => {
  const routes = {
    deviceDamage: "/admin/reports/device-damage",
    activityLogs: "/admin/reports/activity-logs",
    userActivity: "/admin/reports/user-activity",
    borrowStats: "/admin/reports/borrow-statistics",
    systemReports: "/admin/reports/system",
    financial: "/admin/reports/financial",
    stock: "/admin/reports/stock",
  };

  router.push(routes[reportType]);
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
};
</script>
