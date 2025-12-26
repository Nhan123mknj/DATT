<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Quản lý Phiếu trả</h1>
        <p class="text-gray-600 mt-1">Danh sách các phiếu trả thiết bị</p>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Từ ngày
            </label>
            <input
              v-model="filters.from_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Đến ngày
            </label>
            <input
              v-model="filters.to_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div class="flex items-end gap-2">
            <button
              @click="applyFilters"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
              Lọc
            </button>
            <button
              @click="clearFilters"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Xóa lọc
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <TableLoading v-if="isLoading" />

        <div v-else-if="returnSlips.length === 0" class="p-8 text-center">
          <p class="text-gray-500">Không có phiếu trả nào</p>
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
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
                Người mượn
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                Ngày trả
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                Tình trạng
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                Trễ hạn
              </th>

              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                Điểm tín nhiệm
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                Nhân viên
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                Thao tác
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="returnSlip in returnSlips"
              :key="returnSlip.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-medium text-gray-900">
                  #{{ returnSlip.borrow_id }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">
                  {{ returnSlip.borrow?.borrower?.name }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ returnSlip.borrow?.borrower?.email }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(returnSlip.return_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getConditionClass(returnSlip.overall_condition)"
                >
                  {{ getConditionLabel(returnSlip.overall_condition) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="returnSlip.late_days > 0"
                  class="text-sm text-red-600 font-medium"
                >
                  {{ returnSlip.late_days }} ngày
                </span>
                <span v-else class="text-sm text-green-600"> Đúng hạn </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="text-sm font-medium"
                  :class="
                    returnSlip.credit_score_change >= 0
                      ? 'text-green-600'
                      : 'text-red-600'
                  "
                >
                  {{ returnSlip.credit_score_change >= 0 ? "+" : ""
                  }}{{ returnSlip.credit_score_change }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ returnSlip.returned_by_staff?.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button
                  @click="viewDetails(returnSlip)"
                  class="text-indigo-600 hover:text-indigo-900 font-medium"
                >
                  Chi tiết
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        v-if="!isLoading && returnSlips.length > 0"
        :pagination="pagination"
        @page-changed="loadReturnSlips"
        class="mt-6"
      />
    </div>

    <!-- <ReturnSlipDetailModal
      :show="showDetailModal"
      :returnSlip="selectedReturnSlip"
      @close="closeDetailModal"
    /> -->
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useReturnSlips } from "../../composables/fetchData/staff/useReturnSlips";
import TableLoading from "../../components/common/TableLoading.vue";
import Pagination from "../../components/common/Pagination.vue";
import useFormatDate from "../../composables/utils/formatDate";

const {
  returnSlips,
  isLoading,
  pagination,
  filters,
  loadReturnSlips,
  applyFilters,
  clearFilters,
} = useReturnSlips();

const { formatDate } = useFormatDate();

onMounted(() => {
  loadReturnSlips();
});

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

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
};

import { useRouter } from "vue-router";

const router = useRouter();

const viewDetails = (returnSlip) => {
  router.push(`/staff/return-slips/${returnSlip.id}`);
};
</script>
