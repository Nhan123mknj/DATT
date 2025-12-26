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
        📋 Lịch Sử Hoạt Động
      </h1>
      <p class="text-gray-600">
        Theo dõi mọi thay đổi và hoạt động trong hệ thống
      </p>
    </div>

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
            >Loại hoạt động</label
          >
          <select
            v-model="filters.logName"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Tất cả</option>
            <option
              v-for="(count, type) in activityTypes"
              :key="type"
              :value="type"
            >
              {{ getActivityTypeLabel(type) }} ({{ count }})
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

    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
        ></div>
        <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
      </div>
    </div>

    <div v-else>
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        <div
          v-for="(count, type) in activityTypes"
          :key="type"
          class="bg-white rounded-lg shadow-sm p-4 border border-gray-100"
        >
          <p class="text-xs text-gray-600 mb-1">
            {{ getActivityTypeLabel(type) }}
          </p>
          <p class="text-2xl font-bold text-gray-900">{{ count }}</p>
        </div>
      </div>

      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            Danh sách hoạt động ({{ pagination.total }})
          </h2>

          <div v-if="activities.length === 0" class="text-center py-12">
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
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <p class="mt-2 text-gray-600">Không có hoạt động nào</p>
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="activity in activities"
              :key="activity.id"
              class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <span
                      :class="getActivityBadgeClass(activity.log_name)"
                      class="px-3 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getActivityTypeLabel(activity.log_name) }}
                    </span>
                    <span class="text-sm text-gray-600">
                      {{ activity.created_at }}
                    </span>
                    <span class="text-xs text-gray-500">
                      ({{ activity.created_at_human }})
                    </span>
                  </div>

                  <p class="text-sm text-gray-900 mb-2">
                    {{ activity.description }}
                  </p>

                  <div class="flex items-center gap-4 text-xs text-gray-600">
                    <div v-if="activity.causer" class="flex items-center gap-1">
                      <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                      </svg>
                      <span>{{ activity.causer.name }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                      <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                        />
                      </svg>
                      <span
                        >{{ getSubjectType(activity.subject_type) }} #{{
                          activity.subject_id
                        }}</span
                      >
                    </div>
                  </div>

                  <div
                    v-if="
                      activity.properties &&
                      Object.keys(activity.properties).length > 0
                    "
                    class="mt-3"
                  >
                    <button
                      @click="toggleProperties(activity.id)"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      {{
                        expandedActivities.includes(activity.id)
                          ? "▼ Ẩn chi tiết"
                          : "▶ Xem chi tiết"
                      }}
                    </button>

                    <div
                      v-if="expandedActivities.includes(activity.id)"
                      class="mt-2 bg-gray-50 rounded p-3"
                    >
                      <div class="space-y-2 text-sm">
                        <div
                          v-if="activity.properties.borrower_name"
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Người mượn:</span
                          >
                          <span>{{ activity.properties.borrower_name }}</span>
                          <span
                            v-if="activity.properties.borrower_email"
                            class="text-gray-600"
                            >({{ activity.properties.borrower_email }})</span
                          >
                        </div>

                        <div
                          v-if="activity.properties.device_count"
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Số lượng thiết bị:</span
                          >
                          <span>{{ activity.properties.device_count }}</span>
                        </div>

                        <div
                          v-if="
                            activity.properties.devices &&
                            activity.properties.devices.length > 0
                          "
                          class="mt-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Danh sách thiết bị:</span
                          >
                          <ul class="mt-1 ml-4 list-disc space-y-1">
                            <li
                              v-for="(device, idx) in activity.properties
                                .devices"
                              :key="idx"
                            >
                              {{ device.name }}
                              <span v-if="device.serial" class="text-gray-600"
                                >(SN: {{ device.serial }})</span
                              >
                              <span
                                v-if="device.condition"
                                class="ml-2 text-xs px-2 py-0.5 rounded"
                                :class="getConditionClass(device.condition)"
                              >
                                {{ getConditionLabel(device.condition) }}
                              </span>
                              <span
                                v-if="
                                  device.damage_fee && device.damage_fee > 0
                                "
                                class="ml-2 text-red-600"
                              >
                                Phí: {{ formatCurrency(device.damage_fee) }}
                              </span>
                            </li>
                          </ul>
                        </div>

                        <div
                          v-if="activity.properties.reserved_from"
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Thời gian đặt:</span
                          >
                          <span
                            >{{ activity.properties.reserved_from }} -
                            {{ activity.properties.reserved_until }}</span
                          >
                        </div>

                        <div
                          v-if="activity.properties.expected_return_date"
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Ngày dự kiến trả:</span
                          >
                          <span>{{
                            activity.properties.expected_return_date
                          }}</span>
                        </div>

                        <div
                          v-if="activity.properties.return_date"
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Ngày trả thực tế:</span
                          >
                          <span>{{ activity.properties.return_date }}</span>
                        </div>

                        <!-- Source -->
                        <div
                          v-if="activity.properties.source"
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Nguồn:</span
                          >
                          <span>{{ activity.properties.source }}</span>
                        </div>

                        <div
                          v-if="activity.properties.overall_condition"
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Tình trạng chung:</span
                          >
                          <span
                            :class="
                              getConditionClass(
                                activity.properties.overall_condition
                              )
                            "
                          >
                            {{
                              getConditionLabel(
                                activity.properties.overall_condition
                              )
                            }}
                          </span>
                        </div>

                        <div
                          v-if="
                            activity.properties.total_damage_fee &&
                            activity.properties.total_damage_fee > 0
                          "
                          class="flex gap-2"
                        >
                          <span class="font-semibold text-gray-700"
                            >Tổng phí hư hỏng:</span
                          >
                          <span class="text-red-600 font-semibold">{{
                            formatCurrency(activity.properties.total_damage_fee)
                          }}</span>
                        </div>

                        <details class="mt-3">
                          <summary
                            class="cursor-pointer text-xs text-gray-500 hover:text-gray-700"
                          >
                            Xem dữ liệu gốc (JSON)
                          </summary>
                          <pre
                            class="text-xs text-gray-700 overflow-x-auto mt-2 bg-white p-2 rounded border"
                            >{{
                              JSON.stringify(activity.properties, null, 2)
                            }}</pre
                          >
                        </details>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="pagination.last_page > 1"
            class="mt-6 flex items-center justify-between"
          >
            <div class="text-sm text-gray-600">
              Trang {{ pagination.current_page }} /
              {{ pagination.last_page }} ({{ pagination.total }} hoạt động)
            </div>
            <div class="flex gap-2">
              <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Trước
              </button>
              <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Sau
              </button>
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
  period: "7days",
  fromDate: "",
  toDate: "",
  logName: "",
});

const activities = ref([]);
const activityTypes = ref({});
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 50,
  total: 0,
});
const expandedActivities = ref([]);
const isLoading = ref(false);

onMounted(async () => {
  await loadData();
});

const loadData = async (page = 1) => {
  try {
    isLoading.value = true;

    const params = {
      period: filters.value.period,
      page: page,
    };

    if (filters.value.period === "custom") {
      params.from_date = filters.value.fromDate;
      params.to_date = filters.value.toDate;
    }

    if (filters.value.logName) {
      params.log_name = filters.value.logName;
    }

    const response = await reportsService.getActivityLogs(params);

    activities.value = response.data.activities;
    activityTypes.value = response.data.activity_types;
    console.log(activityTypes.value);

    pagination.value = response.data.pagination;
  } catch (error) {
    console.error("Error loading activity logs:", error);
    toast.error("Không thể tải lịch sử hoạt động");
  } finally {
    isLoading.value = false;
  }
};

const applyFilters = () => {
  loadData(1);
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadData(page);
  }
};

const toggleProperties = (activityId) => {
  const index = expandedActivities.value.indexOf(activityId);
  if (index > -1) {
    expandedActivities.value.splice(index, 1);
  } else {
    expandedActivities.value.push(activityId);
  }
};

const getActivityTypeLabel = (type) => {
  const labels = {
    "device-damaged": "Thiết bị hư hỏng",
    "device-returned-good": "Trả thiết bị tốt",
    reservation: "Phiếu đặt trước",
    borrow: "Trả thiết bị",

    default: "Hoạt động khác",
  };
  return labels[type] || labels["default"];
};

const getActivityBadgeClass = (type) => {
  const classes = {
    "device-damaged": "bg-red-100 text-red-800",
    "device-returned-good": "bg-green-100 text-green-800",
    "borrow-created": "bg-blue-100 text-blue-800",
    "borrow-approved": "bg-purple-100 text-purple-800",
    "borrow-issued": "bg-yellow-100 text-yellow-800",
    "borrow-returned": "bg-green-100 text-green-800",
    "borrow-canceled": "bg-gray-100 text-gray-800",
  };
  return classes[type] || "bg-gray-100 text-gray-800";
};

const getSubjectType = (type) => {
  const types = {
    "App\\Models\\DeviceUnits": "Thiết bị",
    "App\\Models\\Borrows": "Phiếu mượn",
    "App\\Models\\DeviceReservation": "Phiếu đặt trước",
    "App\\Models\\ReturnSlip": "Phiếu trả",
    "App\\Models\\User": "Người dùng",
  };
  return types[type] || "Đối tượng";
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
};

const getConditionLabel = (condition) => {
  const labels = {
    good: "Tốt",
    minor_damage: "Hư hỏng nhẹ",
    major_damage: "Hư hỏng nặng",
    broken: "Hỏng",
    borrowed: "Đang mượn",
    returned: "Đã trả",
  };
  return labels[condition] || condition;
};

const getConditionClass = (condition) => {
  const classes = {
    good: "bg-green-100 text-green-800",
    minor_damage: "bg-yellow-100 text-yellow-800",
    major_damage: "bg-orange-100 text-orange-800",
    broken: "bg-red-100 text-red-800",
  };
  return classes[condition] || "bg-gray-100 text-gray-800";
};
</script>
