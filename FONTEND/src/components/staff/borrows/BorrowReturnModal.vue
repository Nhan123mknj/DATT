<template>
  <ModalForm
    :show="show"
    title="Xác nhận trả thiết bị"
    @close="$emit('close')"
    @submit="$emit('submit')"
  >
    <div class="space-y-6">
      <div
        class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-xl p-5 shadow-sm"
      >
        <div class="flex items-start gap-4">
          <div
            class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 text-indigo-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-900 text-lg mb-2">
              Thông tin phiếu mượn
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <span class="text-gray-600">Người mượn:</span>
                <p class="font-semibold text-gray-900 mt-0.5">
                  {{ borrow.borrower?.name }}
                </p>
              </div>
              <div>
                <span class="text-gray-600">Ngày trả dự kiến:</span>
                <p class="font-semibold text-gray-900 mt-0.5">
                  {{ formatDate(borrow.expected_return_date) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div>
        <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-gray-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            />
          </svg>
          Kiểm tra tình trạng thiết bị
        </h4>
        <div
          class="border border-gray-200 rounded-xl overflow-hidden shadow-sm"
        >
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th
                  class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
                >
                  Thiết bị
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-48"
                >
                  Tình trạng
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="item in returnItems"
                :key="item.device_unit_id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-4 py-3 align-top">
                  <div class="flex items-start gap-3">
                    <div class="flex-1">
                      <p class="font-semibold text-gray-900">
                        {{ item.device_name }}
                      </p>
                      <p class="text-sm text-gray-500 mt-0.5">
                        SN: {{ item.serial_number }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 align-top">
                  <select
                    v-model="item.condition_status"
                    class="block w-full pl-3 pr-8 py-2 text-sm border-0 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 rounded-md shadow-sm transition-all bg-white"
                    :class="{
                      'text-emerald-700 ring-emerald-200 bg-emerald-50':
                        item.condition_status === 'good',
                      'text-rose-700 ring-rose-200 bg-rose-50':
                        item.condition_status !== 'good',
                    }"
                  >
                    <option value="good">✅ Tốt</option>
                    <option value="minor_damage">⚠️ Hư hỏng nhẹ</option>
                    <option value="major_damage">❌ Hư hỏng nặng</option>
                    <option value="broken">🚫 Hỏng</option>
                  </select>

                  <textarea
                    v-if="item.condition_status !== 'good'"
                    v-model="item.damage_descriptions"
                    placeholder="Mô tả hư hỏng..."
                    rows="2"
                    class="mt-2 block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-600"
                  ></textarea>
                  <input
                    v-if="item.condition_status !== 'good'"
                    v-model.number="item.damage_fee"
                    type="number"
                    min="0"
                    placeholder="Phí hư hỏng (VNĐ)"
                    class="mt-2 block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-600"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- OTP Input -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-3 mb-3">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-blue-600 mt-0.5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
            />
          </svg>
          <div class="flex-1">
            <p class="text-sm font-semibold text-blue-800">
              Mã OTP đã được gửi đến:
            </p>
            <p class="text-sm text-blue-700">{{ borrow.borrower?.email }}</p>
          </div>
        </div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Mã OTP <span class="text-red-500">*</span>
        </label>
        <input
          :value="otp"
          @input="$emit('update:otp', $event.target.value)"
          type="text"
          maxlength="6"
          placeholder="Nhập mã OTP 6 số"
          class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-2xl font-mono tracking-widest"
        />
        <p class="text-xs text-gray-500 mt-2">
          ⏰ Mã OTP có hiệu lực trong 5 phút
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Ghi chú chung cho phiếu trả</label
        >
        <textarea
          :value="notes"
          @input="$emit('update:notes', $event.target.value)"
          rows="3"
          class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
          placeholder="Nhập ghi chú chung..."
        ></textarea>
      </div>

      <div
        v-if="error"
        class="p-4 rounded-lg bg-red-50 border border-red-200 flex items-start gap-3"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 text-red-500 mt-0.5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
        <p class="text-sm text-red-600 font-medium">{{ error }}</p>
      </div>
    </div>
    <template #footer>
      <button
        type="button"
        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors"
        @click="$emit('close')"
      >
        Hủy bỏ
      </button>
      <button
        type="submit"
        class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2"
        :disabled="loading"
      >
        <svg
          v-if="loading"
          class="animate-spin h-4 w-4 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        <span>{{ loading ? "Đang xử lý..." : "Xác nhận trả" }}</span>
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import ModalForm from "../../ModalForm.vue";
import useFormatDate from "../../../composables/utils/formatDate";

const props = defineProps({
  show: Boolean,
  borrow: Object,
  returnItems: Array,
  otp: String,
  notes: String,
  error: String,
  loading: Boolean,
});

defineEmits([
  "close",
  "submit",
  "update:returnItems",
  "update:otp",
  "update:notes",
]);

const { formatDate } = useFormatDate();
</script>
