<template>
  <Modal
    :show="show"
    title="Chi tiết phiếu mượn"
    @close="$emit('close')"
    size="large"
  >
    <div v-if="borrow" class="space-y-5 text-sm text-gray-700">
      <div
        class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100"
      >
        <div>
          <p
            class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
          >
            Mã phiếu
          </p>
          <p class="font-bold text-gray-900 text-lg">#{{ borrow.id }}</p>
        </div>
        <div class="text-right">
          <p
            class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
          >
            Trạng thái
          </p>
          <span
            class="px-3 py-1 rounded-full text-xs font-semibold inline-block"
            :class="statusClasses(borrow.status)"
          >
            {{ statusLabel(borrow.status) }}
          </span>
        </div>
      </div>

      <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
        <h3 class="font-bold text-indigo-900 mb-3 flex items-center">
          <span
            class="w-6 h-6 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center text-xs mr-2"
          >
            <font-awesome-icon icon="user" />
          </span>
          Thông tin người mượn
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <p class="text-gray-500 text-xs mb-1">Họ tên</p>
            <p class="font-medium text-gray-900">
              {{ borrow.borrower?.name || "N/A" }}
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs mb-1">Email</p>
            <p class="font-medium text-gray-900">
              {{ borrow.borrower?.email || "N/A" }}
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs mb-1">Số điện thoại</p>
            <p class="font-medium text-gray-900">
              {{ borrow.borrower?.phone || "N/A" }}
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="p-3 border border-gray-100 rounded-lg">
          <p class="text-gray-500 text-xs mb-1">Ngày mượn</p>
          <p class="font-medium flex items-center gap-2">
            <font-awesome-icon icon="calendar-alt" class="text-indigo-500" />
            {{ formatDate(borrow.borrowed_date) }}
          </p>
        </div>
        <div class="p-3 border border-gray-100 rounded-lg">
          <p class="text-gray-500 text-xs mb-1">Trả dự kiến</p>
          <p class="font-medium flex items-center gap-2">
            <font-awesome-icon icon="calendar-check" class="text-indigo-500" />
            {{ formatDate(borrow.expected_return_date) }}
          </p>
        </div>
      </div>

      <div>
        <p class="font-bold text-gray-900 mb-3 flex items-center gap-2">
          <font-awesome-icon icon="boxes" class="text-gray-400" />
          Danh sách thiết bị
        </p>
        <div
          class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden"
        >
          <ul class="divide-y divide-gray-200">
            <li
              v-for="(detail, index) in borrow.details || []"
              :key="detail.id"
              class="p-3 hover:bg-white transition-colors flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <span
                  class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs font-bold"
                >
                  {{ index + 1 }}
                </span>
                <div>
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
                </div>
              </div>
            </li>
          </ul>
          <div
            v-if="!borrow.details?.length"
            class="p-4 text-center text-gray-500 italic"
          >
            Không có thiết bị nào
          </div>
        </div>
      </div>

      <div
        v-if="borrow.notes"
        class="bg-amber-50 border border-amber-100 rounded-xl p-4"
      >
        <p class="font-bold text-amber-800 mb-1 flex items-center gap-2">
          <font-awesome-icon icon="sticky-note" />
          Ghi chú
        </p>
        <p class="text-amber-900">{{ borrow.notes }}</p>
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
import Modal from "../../Modal.vue";
import useStatusLabel from "../../../composables/utils/statusLabel";
import useFormatDate from "../../../composables/utils/formatDate";

const props = defineProps({
  show: Boolean,
  borrow: Object,
});

defineEmits(["close"]);

const { statusBorrowLabel: statusLabel, statusClasses } = useStatusLabel();
const { formatDate } = useFormatDate();
</script>
