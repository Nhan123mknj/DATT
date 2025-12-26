<template>
  <div class="min-h-screen bg-gray-50 p-6">
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
        🔧 Báo Cáo Hư Hỏng Thiết Bị
      </h1>
      <p class="text-gray-600">
        Phân tích chi tiết về tình trạng hư hỏng và chi phí sửa chữa
      </p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Danh mục thiết bị</label
          >
          <select
            v-model="filters.categoryId"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Tất cả</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
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

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-sm font-medium text-gray-600 mb-2">
            Thiết bị bị ảnh hưởng
          </h3>
          <p class="text-4xl font-bold text-gray-900">
            {{ summary.total_damages }}
          </p>
          <p class="text-xs text-gray-500 mt-2">
            Số lượng thiết bị đã bị hư hỏng
          </p>
        </div>
      </div>

      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                activeTab === tab.id
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              ]"
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <div v-if="activeTab === 'all'">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
              Tất cả hư hỏng ({{ damages.length }})
            </h2>

            <div v-if="damages.length === 0" class="text-center py-12">
              <svg
                class="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                />
              </svg>
              <p class="mt-2 text-gray-600">
                Không có dữ liệu hư hỏng trong khoảng thời gian này
              </p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Thời gian
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Thiết bị
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Người gây ra
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Mức độ
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Thao tác
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="damage in damages"
                    :key="damage.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ damage.damage_date }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ damage.damage_date_human }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ damage.device_name }}
                      </div>
                      <div class="text-xs text-gray-500">
                        SN: {{ damage.serial_number }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900">
                        {{ damage.caused_by.name }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ damage.caused_by.code }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                          getDamageLevelClass(damage.damage_level),
                        ]"
                      >
                        {{ damage.damage_level }}
                      </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      <button
                        @click="viewDetails(damage)"
                        class="text-blue-600 hover:text-blue-900 font-medium"
                      >
                        Chi tiết
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="activeTab === 'devices'">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
              Top thiết bị hay hỏng nhất
            </h2>

            <div
              v-if="topDamagedDevices.length === 0"
              class="text-center py-12"
            >
              <p class="text-gray-600">Không có dữ liệu</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="(device, index) in topDamagedDevices"
                :key="device.device_unit_id"
                class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex-shrink-0">
                  <div
                    class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg"
                  >
                    {{ index + 1 }}
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ device.device_name }}
                  </h3>
                  <p class="text-sm text-gray-600">
                    Serial: {{ device.serial_number }}
                  </p>
                </div>
                <div class="ml-4 flex gap-8">
                  <div class="text-right">
                    <p class="text-xs text-gray-500">Số lần hỏng</p>
                    <p class="text-xl font-bold text-gray-900">
                      {{ device.damage_count }}
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-gray-500">Tổng chi phí</p>
                    <p class="text-xl font-bold text-red-600">
                      {{ formatCurrency(device.total_cost) }}
                    </p>
                  </div>
                </div>
                <button
                  @click="viewDeviceHistory(device)"
                  class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                >
                  Xem lịch sử
                </button>
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'users'">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
              Người dùng hay làm hỏng thiết bị
            </h2>

            <div v-if="topDamagingUsers.length === 0" class="text-center py-12">
              <p class="text-gray-600">Không có dữ liệu</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="(user, index) in topDamagingUsers"
                :key="user.id"
                class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex-shrink-0">
                  <div
                    class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-lg"
                  >
                    {{ index + 1 }}
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ user.name }}
                  </h3>
                  <p class="text-sm text-gray-600">
                    {{ user.code }} - {{ user.email }}
                  </p>
                </div>
                <div class="ml-4 flex gap-8">
                  <div class="text-right">
                    <p class="text-xs text-gray-500">Số lần làm hỏng</p>
                    <p class="text-xl font-bold text-gray-900">
                      {{ user.damage_count }}
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-gray-500">Tổng chi phí gây ra</p>
                    <p class="text-xl font-bold text-red-600">
                      {{ formatCurrency(user.total_cost) }}
                    </p>
                  </div>
                </div>
                <button
                  @click="viewUserActivity(user)"
                  class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                >
                  Xem hoạt động
                </button>
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'category'">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
              Thống kê theo danh mục
            </h2>

            <div
              v-if="damagesByCategory.length === 0"
              class="text-center py-12"
            >
              <p class="text-gray-600">Không có dữ liệu</p>
            </div>

            <div
              v-else
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            >
              <div
                v-for="cat in damagesByCategory"
                :key="cat.id"
                class="bg-gray-50 rounded-lg p-6"
              >
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                  {{ cat.name }}
                </h3>

                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Số lần hư hỏng:</span>
                    <span class="text-lg font-bold text-gray-900">{{
                      cat.damage_count
                    }}</span>
                  </div>

                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Chi phí:</span>
                    <span class="text-lg font-bold text-red-600">{{
                      formatCurrency(cat.total_cost)
                    }}</span>
                  </div>

                  <div class="mt-4">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-xs text-gray-500"
                        >{{ cat.percentage }}% tổng hư hỏng</span
                      >
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: cat.percentage + '%' }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
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

const filters = ref({
  period: "30days",
  fromDate: "",
  toDate: "",
  categoryId: "",
});

const activeTab = ref("all");
const tabs = [
  { id: "all", label: "Tất cả hư hỏng" },
  { id: "devices", label: "Theo thiết bị" },
  { id: "users", label: "Theo người dùng" },
  { id: "category", label: "Theo danh mục" },
];

const summary = ref({
  totalDamages: 0,
  totalCost: 0,
  affectedDevices: 0,
  avgCost: 0,
});

const categories = ref([]);
const damages = ref([]);
const topDamagedDevices = ref([]);
const topDamagingUsers = ref([]);
const damagesByCategory = ref([]);
const isLoading = ref(false);

onMounted(async () => {
  await loadData();
});

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

    if (filters.value.categoryId) {
      params.category_id = filters.value.categoryId;
    }

    const response = await reportsService.getDeviceDamageReports(params);

    summary.value = response.data.summary;
    damages.value = response.data.all_damages;
    topDamagedDevices.value = response.data.top_damaged_devices;
    topDamagingUsers.value = response.data.top_damaging_users;
    damagesByCategory.value = response.data.damages_by_category;
  } catch (error) {
    console.error("Error loading damage reports:", error);
    toast.error("Không thể tải báo cáo hư hỏng");
  } finally {
    isLoading.value = false;
  }
};

const applyFilters = () => {
  loadData();
};

const viewDetails = (damage) => {
  router.push(`/admin/reports/device-damage/${damage.id}`);
};

const viewDeviceHistory = (device) => {
  router.push(`/admin/reports/device-damage/${device.device_unit_id}`);
};

const viewUserActivity = (user) => {
  router.push(`/admin/reports/user-activity/${user.id}`);
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
};

const getDamageLevelClass = (level) => {
  const classes = {
    "Hư hỏng nhẹ": "bg-yellow-100 text-yellow-800",
    "Hư hỏng nặng": "bg-orange-100 text-orange-800",
    "Hỏng hoàn toàn": "bg-red-100 text-red-800",
  };
  return classes[level] || "bg-gray-100 text-gray-800";
};
</script>
