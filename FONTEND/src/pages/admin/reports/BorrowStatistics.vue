<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-6">
      <button
        @click="$router.push('/admin/reports')"
        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
      >
        <svg
          class="w-4 h-4 mr-1"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        Quay lại Báo cáo
      </button>
      <h1 class="text-3xl font-bold text-gray-900 mb-2">
        📊 Thống Kê Mượn Trả
      </h1>
      <p class="text-gray-600">
        Phân tích xu hướng và hiệu suất mượn trả thiết bị
      </p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Khoảng thời gian</label
          >
          <select
            v-model="filters.period"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="7days">7 ngày qua</option>
            <option value="30days">30 ngày qua</option>
            <option value="3months">3 tháng qua</option>
            <option value="6months">6 tháng qua</option>
            <option value="1year">1 năm qua</option>
            <option value="custom">Tùy chỉnh</option>
          </select>
        </div>

        <div v-if="filters.period === 'custom'">
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Từ ngày</label
          >
          <input
            v-model="filters.fromDate"
            type="date"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div v-if="filters.period === 'custom'">
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Đến ngày</label
          >
          <input
            v-model="filters.toDate"
            type="date"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div class="flex items-end">
          <button
            @click="applyFilters"
            class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            Áp dụng
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
        ></div>
        <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
      </div>
    </div>

    <!-- Content -->
    <div v-else>
      <!-- Summary Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-sm font-medium text-gray-600 mb-2">
            Tổng số lượt mượn
          </h3>
          <p class="text-4xl font-bold text-gray-900">
            {{ summary.total_borrows }}
          </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-sm font-medium text-gray-600 mb-2">
            Phiếu đã phát hành
          </h3>
          <p class="text-4xl font-bold text-purple-600">
            {{ summary.issued_borrows || 0 }}
          </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-sm font-medium text-gray-600 mb-2">Đang mượn</h3>
          <p class="text-4xl font-bold text-orange-600">
            {{ summary.completed_borrows }}
          </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-sm font-medium text-gray-600 mb-2">Đã hoàn thành</h3>
          <p class="text-4xl font-bold text-green-600">
            {{ summary.on_time_borrows }}
          </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-sm font-medium text-gray-600 mb-2">Trả trễ</h3>
          <p class="text-4xl font-bold text-red-600">
            {{ summary.late_borrows }}
          </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-sm font-medium text-gray-600 mb-2">Tỷ lệ đúng hạn</h3>
          <p class="text-4xl font-bold text-green-600">
            {{ summary.on_time_rate }}%
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            Thiết bị được mượn nhiều nhất
          </h2>

          <div
            v-if="mostBorrowedDevices.length === 0"
            class="text-center py-8 text-gray-500"
          >
            Không có dữ liệu
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(device, index) in mostBorrowedDevices"
              :key="device.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm"
                >
                  {{ index + 1 }}
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ device.name }}</p>
                  <p class="text-xs text-gray-500">
                    {{ device.borrow_count }} lượt mượn
                  </p>
                </div>
              </div>
              <div class="text-right">
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all"
                    :style="{
                      width:
                        (device.borrow_count /
                          mostBorrowedDevices[0].borrow_count) *
                          100 +
                        '%',
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            Người mượn nhiều nhất
          </h2>

          <div
            v-if="topBorrowers.length === 0"
            class="text-center py-8 text-gray-500"
          >
            Không có dữ liệu
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(user, index) in topBorrowers"
              :key="user.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm"
                >
                  {{ index + 1 }}
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-500">
                    {{ user.code }} - {{ user.email }}
                  </p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold text-gray-900">
                  {{ user.borrow_count }} lượt
                </p>
                <p class="text-xs text-green-600">
                  {{ user.on_time_count }} đúng hạn
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            Phân loại theo trạng thái
          </h2>

          <div class="space-y-4">
            <div
              v-for="(count, status) in borrowsByStatus"
              :key="status"
              class="space-y-2"
            >
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700 capitalize">{{
                  getStatusLabel(status)
                }}</span>
                <span class="text-sm font-bold text-gray-900">{{ count }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  :class="getStatusColor(status)"
                  class="h-2 rounded-full transition-all"
                  :style="{
                    width: (count / summary.total_borrows) * 100 + '%',
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            Xu hướng mượn (7 ngày qua)
          </h2>

          <canvas ref="trendChartCanvas" style="max-height: 250px"></canvas>
        </div>
      </div>

      <Button
        @click="exportBorrowedCSV"
        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
      >
        Xuất CSV
      </Button>
      <div
        class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mt-6"
      >
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
          Số người mượn {{ data.count }}
        </h2>

        <div
          v-if="activeBorrows.length === 0"
          class="text-center py-8 text-gray-500"
        >
          Không có phiếu mượn nào đang hoạt động
        </div>

        <div v-else class="overflow-x-auto">
          <table
            class="table-auto w-full bg-white border border-gray-300 border-collapse"
          >
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Người mượn
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Số phiếu mượn
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Số Thiết bị đang mượn
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Chi tiết thiết bị
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in data.data"
                :key="item.borrower.id"
                class="border-b border-gray-200 hover:bg-gray-50"
              >
                <td>{{ item.borrower.name }}</td>
                <td>{{ item.borrow_count }}</td>
                <td>{{ item.total_devices }}</td>
                <td>
                  <ul>
                    <li v-for="(d, i) in item.details" :key="i">
                      {{ d.device_unit.serial_number }}
                    </li>
                  </ul>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from "vue";
import { useToast } from "vue-toastification";
import { reportsService } from "../../../services/admin/reportsService";
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend,
  Filler,
} from "chart.js";
import Button from "../../../components/common/Button.vue";
import apiClient from "../../../services/api/apiClient";

Chart.register(
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend,
  Filler
);
const getBorrowedMyList = async () => {
  const response = await reportsService.getDetailBorrows();
  data.value = response.data;
  console.log(data.value);
};
// const getBorrowedMyList = async () => {
//   const response = await apiClient.get(`/admin/reports/borrows/me`);

//   data.value = response.data;
//   console.log(response.data);
//   return response.data;
// };
// const getDeviceReservationList = async () => {
//   const response = await apiClient.get(`/admin/reports/reservations`);

//   data.value = response.data;
//   console.log(response.data);
//   return response.data;
// };
const toast = useToast();

const trendChartCanvas = ref(null);
let trendChartInstance = null;

const filters = ref({
  period: "30days",
  fromDate: "",
  toDate: "",
});
const data = ref([]);
// const getBorrowedByUser = async () => {
//   const response = await reportsService.getDetailBorrows();
//   data.value = response.data;
//   console.log(data.value);
// };

const summary = ref({
  total_borrows: 0,
  completed_borrows: 0,
  on_time_borrows: 0,
  late_borrows: 0,
  on_time_rate: 0,
});

const borrowsByStatus = ref({});
const mostBorrowedDevices = ref([]);
const topBorrowers = ref([]);
const borrowsOverTime = ref([]);
const borrows = ref([]);
const isLoading = ref(false);

const activeBorrows = computed(() => {
  return borrows.value.filter((borrow) =>
    ["approved", "completed", "overdue"].includes(borrow.status)
  );
});
const exportBorrowedCSV = () => {
  const rows = [];

  rows.push([
    "Người mượn",
    "Số phiếu mượn",
    "Tổng thiết bị",
    "Danh sách Serial",
  ]);

  data.value.data.forEach((item) => {
    const serials = item.details
      .map((d) => d.device_unit.serial_number)
      .join(", ");

    rows.push([
      item.borrower.name,
      item.borrow_count,
      item.total_devices,
      serials,
    ]);
  });
  const BOM = "\uFEFF";
  const csvContent =
    BOM +
    rows.map((row) => row.map((cell) => `"${cell}"`).join(",")).join("\n");

  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);

  const link = document.createElement("a");
  link.href = url;
  link.download = "thong_ke_nguoi_muon.csv";
  link.click();

  URL.revokeObjectURL(url);
};

// const maxBorrowCount = computed(() => {
//   return Math.max(...borrowsOverTime.value.map((item) => item.count), 1);
// });

const initTrendChart = () => {
  if (!trendChartCanvas.value || borrowsOverTime.value.length === 0) return;

  if (trendChartInstance) {
    trendChartInstance.destroy();
  }

  const ctx = trendChartCanvas.value.getContext("2d");
  trendChartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: borrowsOverTime.value.map((item) => item.date),
      datasets: [
        {
          label: "Số lượt mượn",
          data: borrowsOverTime.value.map((item) => item.count),
          borderColor: "rgba(59, 130, 246, 1)",
          backgroundColor: "rgba(59, 130, 246, 0.1)",
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          pointHoverRadius: 5,
          pointBackgroundColor: "rgba(59, 130, 246, 1)",
          borderRadius: 5,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return context.parsed.y + " lượt mượn";
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
        x: {
          ticks: {
            maxRotation: 45,
            minRotation: 45,
          },
        },
      },
    },
  });
};

onMounted(async () => {
  await loadData();
  await getBorrowedMyList();
  // await getDeviceReservationList();
});
const loadDetailBorrow = async () => {
  try {
    isLoading.value = true;
    const response = await reportsService.getDetailBorrows();
    console.log(response.data);
  } catch (error) {
    console.error("Error loading borrow detail:", error);
    toast.error("Không thể tải chi tiết phiếu mượn");
  } finally {
    isLoading.value = false;
  }
};

const loadData = async () => {
  try {
    isLoading.value = true;

    const params = {
      period: filters.value.period,
    };

    if (filters.value.period === "custom") {
      params.from_date = filters.value.fromDate;
      params.to_date = filters.value.toDate;
    }

    const response = await reportsService.getBorrowStatistics(params);

    summary.value = response.data.summary;
    borrowsByStatus.value = response.data.borrows_by_status;
    mostBorrowedDevices.value = response.data.most_borrowed_devices;
    topBorrowers.value = response.data.top_borrowers;
    borrowsOverTime.value = response.data.borrows_over_time;
    borrows.value = response.data.borrows || [];

    setTimeout(() => {
      initTrendChart();
    }, 100);
  } catch (error) {
    console.error("Error loading borrow statistics:", error);
    toast.error("Không thể tải thống kê mượn trả");
  } finally {
    isLoading.value = false;
  }
};

const applyFilters = () => {
  loadData();
};

const getStatusLabel = (status) => {
  const labels = {
    pending: "Chờ duyệt",
    approved: "Đã duyệt",
    completed: "Đang mượn",
    returned: "Đã trả",
    overdue: "Quá hạn",
    canceled: "Đã hủy",
  };
  return labels[status] || status;
};

const getStatusColor = (status) => {
  const colors = {
    pending: "bg-yellow-500",
    approved: "bg-blue-500",
    completed: "bg-purple-500",
    returned: "bg-green-500",
    overdue: "bg-red-500",
    canceled: "bg-gray-500",
  };
  return colors[status] || "bg-gray-500";
};

const isOverdue = (borrow) => {
  if (!borrow.expected_return_date) return false;
  const expectedDate = new Date(borrow.expected_return_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return (
    expectedDate < today &&
    ["approved", "completed", "overdue"].includes(borrow.status)
  );
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN", {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
    });
  } catch {
    return dateString;
  }
};

const getDeviceNames = (borrow) => {
  if (!borrow.details || !Array.isArray(borrow.details)) {
    return "N/A";
  }
  const deviceNames = borrow.details
    .map(
      (detail) =>
        detail.device_unit?.device?.name || detail.device_unit?.name || "N/A"
    )
    .filter((name) => name !== "N/A");
  return deviceNames.length > 0 ? deviceNames.join(", ") : "N/A";
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-blue-100 text-blue-800",
    completed: "bg-purple-100 text-purple-800",
    returned: "bg-green-100 text-green-800",
    overdue: "bg-red-100 text-red-800",
    canceled: "bg-gray-100 text-gray-800",
  };
  return classes[status] || "bg-gray-100 text-gray-800";
};

// Watch for borrowsOverTime changes
watch(borrowsOverTime, () => {
  if (borrowsOverTime.value.length > 0) {
    setTimeout(() => {
      initTrendChart();
    }, 100);
  }
});

onUnmounted(() => {
  if (trendChartInstance) {
    trendChartInstance.destroy();
  }
});
</script>
