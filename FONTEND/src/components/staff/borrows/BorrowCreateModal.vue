<template>
  <ModalForm
    :show="show"
    title="Tạo phiếu mượn nhanh"
    @close="$emit('close')"
    @submit="$emit('submit')"
    size="large"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Người mượn <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input
            v-if="!form.borrower_id"
            v-model="form.borrower_search"
            @input="$emit('searchBorrowers')"
            type="text"
            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
            placeholder="Nhập tên, email hoặc mã người mượn..."
          />

          <div
            v-if="borrowerResults.length > 0 && !form.borrower_id"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-100 rounded-xl shadow-lg max-h-60 overflow-y-auto"
          >
            <button
              v-for="user in borrowerResults"
              :key="user.id"
              type="button"
              class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b border-gray-50 last:border-0 transition-colors group"
              @click="$emit('selectBorrower', user)"
            >
              <div class="flex justify-between items-center">
                <div>
                  <p
                    class="font-medium text-gray-900 group-hover:text-indigo-700"
                  >
                    {{ user.name }}
                  </p>
                  <p class="text-xs text-gray-500 group-hover:text-indigo-500">
                    {{ user.email }}
                  </p>
                </div>
                <span
                  class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 group-hover:bg-indigo-100 group-hover:text-indigo-700"
                >
                  {{ user.code || "N/A" }}
                </span>
              </div>
            </button>
          </div>
        </div>

        <!-- Selected User Card -->
        <div
          v-if="form.borrower_id"
          class="mt-2 bg-indigo-50 border border-indigo-200 rounded-xl p-4 relative group"
        >
          <button
            type="button"
            class="absolute top-2 right-2 p-1 text-indigo-400 hover:text-red-500 hover:bg-white rounded-full transition-all opacity-0 group-hover:opacity-100"
            @click="$emit('clearBorrower')"
            title="Bỏ chọn"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </button>

          <div class="flex items-center gap-3">
            <div
              class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold"
            >
              {{ form.borrower_name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p
                class="text-sm font-semibold text-indigo-900 flex items-center gap-2"
              >
                {{ form.borrower_name }}
                <span
                  class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-medium uppercase tracking-wider"
                >
                  {{
                    form.borrower_role === "student"
                      ? "Sinh viên"
                      : form.borrower_role === "teacher"
                      ? "Giảng viên"
                      : "Người dùng"
                  }}
                </span>
              </p>
              <p class="text-xs text-indigo-600 font-medium">
                {{ form.borrower_email }}
              </p>
            </div>
          </div>
        </div>
        <p v-if="errors.borrower_id" class="text-xs text-red-500 mt-1">
          {{ errors.borrower_id }}
        </p>
      </div>

      <div>
        <div class="flex items-center justify-between mb-4">
          <label class="block text-sm font-medium text-gray-700">
            Thiết bị <span class="text-red-500">*</span>
          </label>
          <button
            type="button"
            class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100"
            @click="$emit('addDeviceGroup')"
          >
            ➕ Thêm nhóm
          </button>
        </div>

        <div class="space-y-4">
          <div
            v-for="(group, groupIndex) in form.deviceGroups"
            :key="groupIndex"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50 relative"
          >
            <button
              v-if="form.deviceGroups.length > 1"
              type="button"
              class="absolute top-2 right-2 text-gray-400 hover:text-red-500"
              @click="$emit('removeDeviceGroup', groupIndex)"
            >
              ✕
            </button>

            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                  Loại thiết bị
                </label>
                <select
                  v-model="group.category_id"
                  class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"
                  @change="$emit('changeCategory', groupIndex)"
                >
                  <option value="">Chọn loại...</option>
                  <option
                    v-for="cat in categories"
                    :key="cat.id"
                    :value="cat.id"
                  >
                    {{ cat.name }}
                  </option>
                </select>
              </div>

              <div v-if="group.category_id && group.devices.length > 0">
                <label class="block text-xs font-medium text-gray-600 mb-1">
                  Thiết bị
                </label>
                <select
                  v-model="group.device_id"
                  class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"
                  @change="$emit('changeDevice', groupIndex)"
                >
                  <option value="">Chọn thiết bị...</option>
                  <option
                    v-for="device in group.devices"
                    :key="device.id"
                    :value="device.id"
                  >
                    {{ device.name }} ({{ device.model }})
                  </option>
                </select>
              </div>

              <div v-if="group.device_id && group.deviceUnits.length > 0">
                <div v-if="group.category_type === 'consumable'">
                  <label class="block text-xs font-medium text-gray-600 mb-1">
                    Số lượng
                  </label>
                  <input
                    type="number"
                    min="0"
                    :max="group.deviceUnits.length"
                    v-model.number="group.quantity"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"
                    @input="$emit('updateQuantity', groupIndex)"
                  />
                  <p class="text-xs text-gray-500 mt-1">
                    Khả dụng: {{ group.deviceUnits.length }}
                  </p>
                </div>

                <div v-else>
                  <label class="block text-xs font-medium text-gray-600 mb-1">
                    Đơn vị thiết bị (chọn nhiều)
                  </label>
                  <div
                    class="max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1"
                  >
                    <label
                      v-for="unit in group.deviceUnits"
                      :key="unit.id"
                      class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-white cursor-pointer"
                      :class="{
                        'bg-indigo-50': group.device_unit_ids.includes(unit.id),
                      }"
                    >
                      <input
                        type="checkbox"
                        :value="unit.id"
                        v-model="group.device_unit_ids"
                        :disabled="unit.status !== 'available'"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded"
                      />
                      <span
                        class="text-sm"
                        :class="
                          unit.status !== 'available'
                            ? 'text-gray-400 line-through'
                            : ''
                        "
                      >
                        {{ unit.serial_number || `Unit #${unit.id}` }}
                      </span>
                      <span
                        v-if="unit.status !== 'available'"
                        class="ml-auto text-xs text-red-600"
                      >
                        {{
                          unit.status === "reserved"
                            ? "Đã đặt"
                            : "Không khả dụng"
                        }}
                      </span>
                    </label>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">
                    Đã chọn: {{ group.device_unit_ids.length }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <p v-if="errors.devices" class="text-xs text-red-500 mt-2">
          {{ errors.devices }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Ngày trả dự kiến <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.expected_return_date"
          type="date"
          class="w-full px-3 py-2 rounded-lg border border-gray-200"
          :min="tomorrow"
        />
        <p v-if="errors.expected_return_date" class="text-xs text-red-500 mt-1">
          {{ errors.expected_return_date }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Ghi chú</label
        >
        <textarea
          v-model="form.notes"
          rows="3"
          class="w-full px-3 py-2 rounded-lg border border-gray-200"
          placeholder="Ghi chú về phiếu mượn..."
        ></textarea>
      </div>

      <p v-if="errors.general" class="text-sm text-red-500">
        {{ errors.general }}
      </p>
    </div>
    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
        @click="$emit('close')"
      >
        Hủy
      </button>
      <button
        type="submit"
        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
        :disabled="loading"
      >
        {{ loading ? "Đang tạo..." : "Tạo phiếu mượn" }}
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import ModalForm from "../../../components/ModalForm.vue";

const props = defineProps({
  show: Boolean,
  form: Object,
  errors: Object,
  loading: Boolean,
  borrowerResults: Array,
  categories: Array,

  tomorrow: String,
});

defineEmits([
  "close",
  "submit",
  "update:form",
  "searchBorrowers",
  "selectBorrower",
  "clearBorrower",
  "addDeviceGroup",
  "removeDeviceGroup",
  "changeCategory",
  "changeDevice",
  "updateQuantity",
]);
</script>
