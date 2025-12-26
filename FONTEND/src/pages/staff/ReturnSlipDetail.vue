<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$router.push('/staff/return-slips')"
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
          Quay lại danh sách
        </button>
        <h1 class="text-3xl font-bold text-gray-900">Chi tiết phiếu trả</h1>
        <p class="text-gray-600 mt-1">
          Thông tin chi tiết về phiếu trả thiết bị
        </p>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <div
            class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"
          ></div>
          <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
        </div>
      </div>

      <!-- Error -->
      <div
        v-else-if="error"
        class="bg-red-50 border border-red-200 rounded-lg p-6 text-center"
      >
        <p class="text-red-600">{{ error }}</p>
      </div>

      <!-- Content -->
      <div v-else-if="returnSlip" class="space-y-6">
        <!-- Return Slip Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Thông tin phiếu trả
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Mã phiếu mượn</p>
              <p class="text-base font-medium text-gray-900">
                #{{ returnSlip.borrow_id }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Ngày trả thực tế</p>
              <p class="text-base font-medium text-gray-900">
                {{ formatDate(returnSlip.return_date) }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Ngày trả dự kiến</p>
              <p class="text-base font-medium text-gray-900">
                {{ formatDate(returnSlip.borrow?.expected_return_date) }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Trễ hạn</p>
              <p
                class="text-base font-medium"
                :class="
                  returnSlip.late_days > 0 ? 'text-red-600' : 'text-green-600'
                "
              >
                {{
                  returnSlip.late_days > 0
                    ? `${returnSlip.late_days} ngày`
                    : "Đúng hạn"
                }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Tình trạng chung</p>
              <span
                class="inline-block px-3 py-1 text-sm font-semibold rounded-full"
                :class="getConditionClass(returnSlip.overall_condition)"
              >
                {{ getConditionLabel(returnSlip.overall_condition) }}
              </span>
            </div>
            <div>
              <p class="text-sm text-gray-500">Thay đổi điểm tín dụng</p>
              <p
                class="text-base font-medium"
                :class="
                  returnSlip.credit_score_change >= 0
                    ? 'text-green-600'
                    : 'text-red-600'
                "
              >
                {{ returnSlip.credit_score_change >= 0 ? "+" : ""
                }}{{ returnSlip.credit_score_change }}
              </p>
            </div>
          </div>
        </div>

        <!-- Borrower Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Thông tin người mượn
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Họ tên</p>
              <p class="text-base font-medium text-gray-900">
                {{ returnSlip.borrow?.borrower?.name }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Email</p>
              <p class="text-base font-medium text-gray-900">
                {{ returnSlip.borrow?.borrower?.email }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Mã số</p>
              <p class="text-base font-medium text-gray-900">
                {{
                  returnSlip.borrow?.borrower?.student_code ||
                  returnSlip.borrow?.borrower?.teacher_code ||
                  "N/A"
                }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Vai trò</p>
              <p class="text-base font-medium text-gray-900">
                {{ getRoleLabel(returnSlip.borrow?.borrower?.role) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Staff Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Thông tin nhân viên
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Nhân viên nhận trả</p>
              <p class="text-base font-medium text-gray-900">
                {{ returnSlip.returned_by_staff?.name || "N/A" }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Email</p>
              <p class="text-base font-medium text-gray-900">
                {{ returnSlip.returned_by_staff?.email || "N/A" }}
              </p>
            </div>
          </div>
        </div>

        <!-- Device Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Chi tiết thiết bị
          </h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                  >
                    Thiết bị
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                  >
                    Serial Number
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                  >
                    Tình trạng khi trả
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                  >
                    Mô tả hư hỏng
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                  >
                    Ghi chú
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="detail in returnSlip.details"
                  :key="detail.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ detail.device_unit?.device?.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ detail.device_unit?.serial_number }}
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="px-3 py-1 text-xs font-semibold rounded-full"
                      :class="getConditionClass(detail.condition_status)"
                    >
                      {{ getConditionLabel(detail.condition_status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    {{ detail.damage_description || "-" }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    {{ detail.condition_notes || "-" }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="returnSlip.notes" class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Ghi chú</h2>
          <p class="text-gray-700">{{ returnSlip.notes }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import apiClient from "../../services/api/apiClient";
import useFormatDate from "../../composables/utils/formatDate";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { formatDate } = useFormatDate();

const isLoading = ref(true);
const error = ref(null);
const returnSlip = ref(null);

onMounted(async () => {
  await loadReturnSlipDetail();
});

const loadReturnSlipDetail = async () => {
  try {
    isLoading.value = true;
    error.value = null;

    const response = await apiClient.get(
      `/staff/return-slips/${route.params.id}`
    );

    returnSlip.value = response.data;
  } catch (err) {
    console.error("Error loading return slip detail:", err);
    error.value = "Không thể tải chi tiết phiếu trả";
    toast.error("Không thể tải chi tiết phiếu trả");
  } finally {
    isLoading.value = false;
  }
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

const getConditionLabel = (condition) => {
  const labels = {
    good: "Tốt",
    minor_damage: "Hư hỏng nhẹ",
    major_damage: "Hư hỏng nặng",
    broken: "Hỏng",
  };
  return labels[condition] || condition;
};

const getRoleLabel = (role) => {
  const labels = {
    student: "Sinh viên",
    teacher: "Giảng viên",
    staff: "Nhân viên",
    admin: "Quản trị viên",
  };
  return labels[role] || role;
};
</script>
