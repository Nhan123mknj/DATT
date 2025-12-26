<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          <font-awesome-icon icon="fa-solid fa-warehouse" class="mr-2" />
          Báo cáo Kho
        </h1>
        <p class="text-gray-600">Thống kê xuất nhập kho thiết bị</p>
      </div>

      <div class="flex gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Từ ngày</label
          >
          <input
            type="date"
            v-model="startDate"
            @change="loadReport"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Đến ngày</label
          >
          <input
            type="date"
            v-model="endDate"
            @change="loadReport"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Loại phiếu</label
          >
          <select
            v-model="movementTypeFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white"
          >
            <option value="">Tất cả</option>
            <option value="PNK">Nhập Chính (PNK)</option>
            <option value="PNT">Nhập Tạm (PNT)</option>
            <option value="PXT">Xuất Tạm (PXT)</option>
            <option value="PXK">Xuất Chính (PXK)</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="text-center py-12">
      <div
        class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
      ></div>
      <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-600">Nhập Chính (PNK)</h3>
            <div
              class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
            >
              <font-awesome-icon
                icon="fa-solid fa-box"
                class="text-green-600"
              />
            </div>
          </div>
          <p class="text-3xl font-bold text-green-600">
            {{ movementSummary.new_devices }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Mua mới</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-600">Nhập Tạm (PNT)</h3>
            <div
              class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
            >
              <font-awesome-icon
                icon="fa-solid fa-rotate-left"
                class="text-blue-600"
              />
            </div>
          </div>
          <p class="text-3xl font-bold text-blue-600">
            {{ movementSummary.returns }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Trả lại từ mượn</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-600">Xuất Tạm (PXT)</h3>
            <div
              class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center"
            >
              <font-awesome-icon
                icon="fa-solid fa-hand-holding"
                class="text-orange-600"
              />
            </div>
          </div>
          <p class="text-3xl font-bold text-orange-600">
            {{ movementSummary.borrows }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Cho mượn</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-600">Xuất Chính (PXK)</h3>
            <div
              class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center"
            >
              <font-awesome-icon
                icon="fa-solid fa-trash"
                class="text-red-600"
              />
            </div>
          </div>
          <p class="text-3xl font-bold text-red-600">
            {{ movementSummary.retired }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Thanh lý</p>
        </div>
      </div>

      <div
        class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8"
      >
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
          Thống kê 6 tháng gần nhất
        </h2>
        <canvas
          ref="chartCanvas"
          class="w-full"
          style="max-height: 300px"
        ></canvas>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">
            Lịch sử xuất/nhập kho
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  Ngày
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  Loại
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  Lý do
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  Thiết bị
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  Serial
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  Người
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  SL
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-if="filteredMovements.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  Không có dữ liệu
                </td>
              </tr>
              <tr
                v-for="(movement, index) in filteredMovements"
                :key="index"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ formatDate(movement.date) }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="
                      movement.type === 'NHẬP KHO'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    "
                    class="px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ movement.type }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ movement.reason }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ movement.device }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                  {{ movement.serial }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ movement.person || movement.admin || "-" }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 font-semibold">
                  {{ movement.quantity }}
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
import { ref, onMounted, watch, onUnmounted, computed } from "vue";
import { reportsService } from "../../../services/admin/reportsService";
import { useToast } from "vue-toastification";
import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend,
} from "chart.js";

// Register Chart.js components
Chart.register(
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend
);

const toast = useToast();

const isLoading = ref(true);
const chartCanvas = ref(null);
let chartInstance = null;

// Set default dates to current month
const now = new Date();
const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

const startDate = ref(firstDay.toISOString().split("T")[0]);
const endDate = ref(lastDay.toISOString().split("T")[0]);
const movementTypeFilter = ref("");

const overview = ref({
  total_devices: 0,
  in_stock: 0,
  borrowed: 0,
  under_maintenance: 0,
  retired: 0,
  stock_percentage: 0,
});

const movements = ref([]);
const monthlyStats = ref([]);
const period = ref({ start_date: startDate.value, end_date: endDate.value });

// Tổng số từng loại phiếu trong khoảng thời gian
const movementSummary = computed(() => {
  const summary = {
    new_devices: 0,
    returns: 0,
    borrows: 0,
    retired: 0,
  };

  movements.value.forEach((m) => {
    if (m.type === "NHẬP KHO" && m.reason === "Mua mới") summary.new_devices++;
    if (m.type === "NHẬP KHO" && m.reason === "Trả lại") summary.returns++;
    if (m.type === "XUẤT KHO" && m.reason === "Cho mượn") summary.borrows++;
    if (m.type === "XUẤT KHO" && m.reason === "Thanh lý") summary.retired++;
  });

  return summary;
});

// Filter movements theo loại phiếu
const filteredMovements = computed(() => {
  if (!movementTypeFilter.value) return movements.value;

  return movements.value.filter((m) => {
    if (movementTypeFilter.value === "PNK")
      return m.type === "NHẬP KHO" && m.reason === "Mua mới";
    if (movementTypeFilter.value === "PNT")
      return m.type === "NHẬP KHO" && m.reason === "Trả lại";
    if (movementTypeFilter.value === "PXT")
      return m.type === "XUẤT KHO" && m.reason === "Cho mượn";
    if (movementTypeFilter.value === "PXK")
      return m.type === "XUẤT KHO" && m.reason === "Thanh lý";
    return true;
  });
});

const initChart = () => {
  if (!chartCanvas.value || monthlyStats.value.length === 0) return;

  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext("2d");
  chartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: monthlyStats.value.map((stat) => stat.month_name),
      datasets: [
        {
          label: "Nhập Chính (PNK)",
          data: monthlyStats.value.map((stat) => stat.new_devices),
          backgroundColor: "rgba(34, 197, 94, 0.8)",
          borderColor: "rgba(34, 197, 94, 1)",
          borderWidth: 1,
        },
        {
          label: "Nhập Tạm (PNT)",
          data: monthlyStats.value.map((stat) => stat.returns),
          backgroundColor: "rgba(59, 130, 246, 0.8)",
          borderColor: "rgba(59, 130, 246, 1)",
          borderWidth: 1,
        },
        {
          label: "Xuất Tạm (PXT)",
          data: monthlyStats.value.map((stat) => stat.borrows),
          backgroundColor: "rgba(251, 146, 60, 0.8)",
          borderColor: "rgba(251, 146, 60, 1)",
          borderWidth: 1,
        },
        {
          label: "Xuất Chính (PXK)",
          data: monthlyStats.value.map((stat) => stat.retired),
          backgroundColor: "rgba(239, 68, 68, 0.8)",
          borderColor: "rgba(239, 68, 68, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "top",
        },
        title: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return (
                context.dataset.label + ": " + context.parsed.y + " thiết bị"
              );
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
      },
    },
  });
};

const loadReport = async () => {
  try {
    isLoading.value = true;
    const response = await reportsService.getStockReport(
      startDate.value,
      endDate.value
    );

    overview.value = response.data.overview;
    movements.value = response.data.movements;
    monthlyStats.value = response.data.monthly_stats;
    period.value = response.data.period;

    // Initialize chart after data is loaded
    setTimeout(() => {
      initChart();
    }, 100);
  } catch (error) {
    console.error("Error loading stock report:", error);
    toast.error("Không thể tải báo cáo kho");
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Watch for monthlyStats changes
watch(monthlyStats, () => {
  if (monthlyStats.value.length > 0) {
    setTimeout(() => {
      initChart();
    }, 100);
  }
});

onMounted(() => {
  loadReport();
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>
