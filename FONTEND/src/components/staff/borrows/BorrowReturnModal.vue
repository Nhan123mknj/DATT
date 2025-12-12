<template>
  <ModalForm
    :show="show"
    title="Xử lý trả thiết bị"
    @close="$emit('close')"
    @submit="$emit('submit')"
    size="xl"
  >
    <div v-if="borrow" class="space-y-6">
      <!-- Info Card -->
      <div
        class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-start justify-between"
      >
        <div>
          <h4 class="text-slate-800 font-bold text-lg mb-1">
            Phiếu mượn #{{ borrow.id }}
          </h4>
          <div class="text-slate-600 text-sm space-y-1">
            <p>
              <span class="font-medium text-slate-700">Người mượn:</span>
              {{ borrow.borrower?.name }}
            </p>
            <p>
              <span class="font-medium text-slate-700">Ngày mượn:</span>
              {{ formatDate(borrow.borrowed_date) }}
            </p>
            <p>
              <span class="font-medium text-slate-700">Hạn trả:</span>
              {{ formatDate(borrow.expected_return_date) }}
            </p>
          </div>
        </div>
        <div class="text-right">
          <span
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white text-slate-700 border border-slate-200 shadow-sm"
          >
            {{ returnItems.length }} thiết bị
          </span>
        </div>
      </div>

      <!-- List of items to return -->
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
                <th
                  class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
                >
                  Ghi chú hư hỏng
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
                  <div class="text-sm font-bold text-gray-900">
                    {{ item.device_name }}
                  </div>
                  <div
                    class="text-xs text-gray-500 font-mono bg-gray-100 inline-block px-1.5 py-0.5 rounded mt-1 border border-gray-200"
                  >
                    {{ item.serial_number }}
                  </div>
                </td>
                <td class="px-4 py-3 align-top">
                  <select
                    v-model="item.condition_at_return"
                    class="block w-full pl-3 pr-8 py-2 text-sm border-0 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 rounded-md shadow-sm transition-all bg-white"
                    :class="{
                      'text-emerald-700 ring-emerald-200 bg-emerald-50':
                        item.condition_at_return === 'good',
                      'text-rose-700 ring-rose-200 bg-rose-50':
                        item.condition_at_return !== 'good',
                    }"
                  >
                    <option value="good">✅ Tốt</option>
                    <option value="damaged">⚠️ Hư hỏng nhẹ</option>
                    <option value="broken">❌ Hư hỏng nặng</option>
                    <option value="lost">🚫 Mất</option>
                  </select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Signatures -->
      <div
        class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100"
      >
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
          <label
            class="block text-sm font-semibold text-gray-700 mb-3 flex items-center justify-between"
          >
            <span>Chữ ký người trả</span>
            <span class="text-xs font-normal text-gray-500">{{
              borrow.borrower?.name
            }}</span>
          </label>
          <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <SignaturePad v-model="signatures.borrower" />
          </div>
        </div>
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
          <label class="block text-sm font-semibold text-gray-700 mb-3"
            >Chữ ký nhân viên</label
          >
          <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <SignaturePad v-model="signatures.staff" />
          </div>
        </div>
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
        {{ loading ? "Đang xử lý..." : "Xác nhận trả & In phiếu" }}
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import ModalForm from "../../ModalForm.vue";
import SignaturePad from "../../../components/common/SignaturePad.vue";
import useFormatDate from "../../../composables/utils/formatDate";

const props = defineProps({
  show: Boolean,
  borrow: Object,
  returnItems: Array,
  signatures: Object,
  notes: String,
  error: String,
  loading: Boolean,
});

defineEmits([
  "close",
  "submit",
  "update:returnItems",
  "update:signatures",
  "update:notes",
]);

const { formatDate } = useFormatDate();
</script>
