<template>
  <Modal
    :show="show"
    title="Chi tiết phiếu trả"
    @close="$emit('close')"
    size="2xl"
  >
    <div v-if="returnSlip" class="text-sm text-gray-700">
      <div class="space-y-5">
        <!-- Header with Return Slip Info -->
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
          <div class="grid grid-cols-2 gap-4 mb-3">
            <div>
              <p
                class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
              >
                Mã phiếu trả
              </p>
              <p class="font-bold text-gray-900 text-lg">
                #{{ returnSlip.id }}
              </p>
              <p class="text-gray-500 text-xs mt-1">
                {{ formatDate(returnSlip.return_date) }}
              </p>
            </div>
            <div class="text-right">
              <p
                class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
              >
                Trạng thái phiếu mượn
              </p>
              <span
                class="px-3 py-1 rounded-full text-xs font-semibold inline-block"
                :class="statusClasses(returnSlip.borrow?.status)"
              >
                {{ statusLabel(returnSlip.borrow?.status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Borrower Information -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
          <h3 class="font-bold text-indigo-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center text-xs mr-2"
            >
              <font-awesome-icon icon="user" />
            </span>
            Thông tin người mượn
          </h3>
          <div class="grid grid-cols-1 gap-3">
            <div>
              <p class="text-gray-500 text-xs mb-1">Họ tên</p>
              <p class="font-medium text-gray-900">
                {{ returnSlip.borrow?.borrower?.name || "N/A" }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 text-xs mb-1">Email</p>
              <p class="font-medium text-gray-900">
                {{ returnSlip.borrow?.borrower?.email || "N/A" }}
              </p>
            </div>
          </div>
        </div>

        <!-- Borrow & Return Dates -->
        <div class="grid grid-cols-2 gap-4">
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Ngày mượn</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon icon="calendar-alt" class="text-indigo-500" />
              {{ formatDate(returnSlip.borrow?.borrowed_date) }}
            </p>
          </div>
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Trả dự kiến</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon
                icon="calendar-check"
                class="text-indigo-500"
              />
              {{ formatDate(returnSlip.borrow?.expected_return_date) }}
            </p>
          </div>
        </div>

        <!-- Return Information -->
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
          <h3 class="font-bold text-green-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-green-200 text-green-700 flex items-center justify-center text-xs mr-2"
            >
              <font-awesome-icon icon="undo" />
            </span>
            Thông tin trả thiết bị
          </h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-gray-500 text-xs mb-1">Ngày trả thực tế</p>
              <p class="font-medium text-gray-900 flex items-center gap-2">
                <font-awesome-icon icon="clock" class="text-green-500" />
                {{ formatDate(returnSlip.return_date) }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 text-xs mb-1">Người nhận thiết bị</p>
              <p class="font-medium text-gray-900">
                {{ returnSlip.returned_by_staff?.name || "N/A" }}
              </p>
              <p class="text-xs text-gray-500">
                {{ returnSlip.returned_by_staff?.email || "" }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 text-xs mb-1">Tình trạng chung</p>
              <span
                class="px-3 py-1 rounded-full text-xs font-semibold inline-block"
                :class="conditionClasses(returnSlip.overall_condition)"
              >
                {{ conditionLabel(returnSlip.overall_condition) }}
              </span>
            </div>
            <div v-if="returnSlip.late_days > 0">
              <p class="text-gray-500 text-xs mb-1">Số ngày trễ</p>
              <p class="font-medium text-red-600 flex items-center gap-2">
                <font-awesome-icon icon="exclamation-triangle" />
                {{ returnSlip.late_days }} ngày
              </p>
            </div>
          </div>
        </div>

        <!-- Credit Score & Penalties -->
        <div
          v-if="
            returnSlip.credit_score_change !== 0 || returnSlip.late_days > 0
          "
          class="bg-amber-50 border border-amber-100 rounded-xl p-4"
        >
          <h3 class="font-bold text-amber-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-amber-200 text-amber-700 flex items-center justify-center text-xs mr-2"
            >
              <font-awesome-icon icon="star" />
            </span>
            Điểm tín dụng & Phạt
          </h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-gray-500 text-xs mb-1">Thay đổi điểm tín dụng</p>
              <p
                class="font-bold text-lg"
                :class="
                  returnSlip.credit_score_change < 0
                    ? 'text-red-600'
                    : 'text-green-600'
                "
              >
                {{ returnSlip.credit_score_change > 0 ? "+" : ""
                }}{{ returnSlip.credit_score_change }}
              </p>
            </div>
            <div v-if="returnSlip.late_fee > 0">
              <p class="text-gray-500 text-xs mb-1">Phí trễ hạn</p>
              <p class="font-medium text-red-600">
                {{ formatCurrency(returnSlip.late_fee) }}
              </p>
            </div>
            <div v-if="returnSlip.total_penalty > 0">
              <p class="text-gray-500 text-xs mb-1">Tổng phạt</p>
              <p class="font-bold text-red-600 text-lg">
                {{ formatCurrency(returnSlip.total_penalty) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Device List -->
        <div>
          <p class="font-bold text-gray-900 mb-3 flex items-center gap-2">
            <font-awesome-icon icon="boxes" class="text-gray-400" />
            Danh sách thiết bị đã trả
          </p>
          <div
            class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden"
          >
            <ul class="divide-y divide-gray-200 max-h-60 overflow-y-auto">
              <li
                v-for="(detail, index) in returnSlip.details || []"
                :key="detail.id"
                class="p-3 hover:bg-white transition-colors"
              >
                <div class="flex items-start justify-between">
                  <div class="flex items-start gap-3 flex-1">
                    <span
                      class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
                    >
                      {{ index + 1 }}
                    </span>
                    <div class="flex-1">
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
                      <div class="mt-2 flex items-center gap-2">
                        <span
                          class="px-2 py-1 rounded text-xs font-semibold"
                          :class="conditionClasses(detail.condition_status)"
                        >
                          {{ conditionLabel(detail.condition_status) }}
                        </span>
                      </div>
                      <p
                        v-if="detail.condition_notes"
                        class="text-xs text-gray-600 mt-1 italic"
                      >
                        {{ detail.condition_notes }}
                      </p>
                      <p
                        v-if="detail.damage_description"
                        class="text-xs text-red-600 mt-1 font-medium"
                      >
                        <font-awesome-icon
                          icon="exclamation-circle"
                          class="mr-1"
                        />
                        {{ detail.damage_description }}
                      </p>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
            <div
              v-if="!returnSlip.details?.length"
              class="p-4 text-center text-gray-500 italic"
            >
              Không có thiết bị nào
            </div>
          </div>
        </div>

        <!-- Staff Information -->
        <div
          v-if="returnSlip.borrow?.created_by || returnSlip.borrow?.issued_by"
          class="bg-blue-50 border border-blue-100 rounded-xl p-4"
        >
          <h3 class="font-bold text-blue-900 mb-3 flex items-center">
            <span
              class="w-6 h-6 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center text-xs mr-2"
            >
              <font-awesome-icon icon="user-tie" />
            </span>
            Thông tin nhân viên
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div v-if="returnSlip.borrow?.created_by">
              <p class="text-gray-500 text-xs mb-1">Người tạo phiếu mượn</p>
              <p class="font-medium text-gray-900">
                {{ returnSlip.borrow.created_by.name }}
              </p>
              <p class="text-xs text-gray-500">
                {{ returnSlip.borrow.created_by.email }}
              </p>
            </div>
            <div v-if="returnSlip.borrow?.issued_by">
              <p class="text-gray-500 text-xs mb-1">Người xuất kho</p>
              <p class="font-medium text-gray-900">
                {{ returnSlip.borrow.issued_by.name }}
              </p>
              <p class="text-xs text-gray-500">
                {{ returnSlip.borrow.issued_by.email }}
              </p>
            </div>
          </div>
        </div>

        <!-- Condition Notes -->
        <div
          v-if="returnSlip.condition_notes"
          class="bg-purple-50 border border-purple-100 rounded-xl p-4"
        >
          <p class="font-bold text-purple-800 mb-1 flex items-center gap-2">
            <font-awesome-icon icon="sticky-note" />
            Ghi chú tình trạng
          </p>
          <p class="text-purple-900">{{ returnSlip.condition_notes }}</p>
        </div>

        <!-- Borrow Notes -->
        <div
          v-if="returnSlip.borrow?.notes"
          class="bg-amber-50 border border-amber-100 rounded-xl p-4"
        >
          <p class="font-bold text-amber-800 mb-1 flex items-center gap-2">
            <font-awesome-icon icon="sticky-note" />
            Ghi chú phiếu mượn
          </p>
          <p class="text-amber-900">{{ returnSlip.borrow.notes }}</p>
        </div>
      </div>
    </div>
    <template #footer>
      <button
        type="button"
        class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors"
        @click="$emit('close')"
      >
        Đóng
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from "vue";
import Modal from "../../Modal.vue";
import useStatusLabel from "../../../composables/utils/statusLabel";
import useFormatDate from "../../../composables/utils/formatDate";

const props = defineProps({
  show: Boolean,
  return: Object,
});

defineEmits(["close"]);

const returnSlip = computed(() => props.return);

const { statusBorrowLabel: statusLabel, statusClasses } = useStatusLabel();
const { formatDate } = useFormatDate();

const conditionLabel = (condition) => {
  const labels = {
    good: "Tốt",
    minor_damage: "Hư hỏng nhẹ",
    major_damage: "Hư hỏng nặng",
    broken: "Hỏng hoàn toàn",
  };
  return labels[condition] || condition;
};

const conditionClasses = (condition) => {
  const classes = {
    good: "bg-green-100 text-green-800",
    minor_damage: "bg-yellow-100 text-yellow-800",
    major_damage: "bg-orange-100 text-orange-800",
    broken: "bg-red-100 text-red-800",
  };
  return classes[condition] || "bg-gray-100 text-gray-800";
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(value);
};
</script>
